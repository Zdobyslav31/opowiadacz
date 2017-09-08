<?php
/**
 * User repository
 */

namespace Repository;

use Silex\Application;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * Class UserRepository.
 *
 * @package Repository
 */
class UserRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * TagRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Change password.
     *
     * @param array              $form Form
     * @param \Silex\Application $app  Silex application
     * @param int                $id   User id
     *
     * @return boolean Result
     */
    public function changePassword($form, Application $app, $id)
    {
        if (!password_verify($form['old_password'], $app['user']->getPassword())) {
            return false;
        }

        $app['security.encoder.bcrypt']->encodePassword($form['password'], '');
        $passwordPlain = $form['password'];
        $password = $app['security.encoder.bcrypt']->encodePassword($passwordPlain, '');
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->update('si_users', 'u')
            ->set('u.password', ':password')
            ->where('u.id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT)
            ->setParameter(':password', $password, \PDO::PARAM_STR);
        $queryBuilder->execute();

        return true;
    }

    /**
     * Loads user by login.
     *
     * @param string $login User login
     * @throws UsernameNotFoundException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function loadUserByLogin($login)
    {
        try {
            $user = $this->getUserByLogin($login);

            if (!$user || !count($user)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $login)
                );
            }

            $roles = $this->getUserRoles($user['id']);

            if (!$roles || !count($roles)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $login)
                );
            }

            return [
                'id' => $user['id'],
                'login' => $user['login'],
                'password' => $user['password'],
                'roles' => $roles,
            ];
        } catch (DBALException $exception) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $login)
            );
        } catch (UsernameNotFoundException $exception) {
            throw $exception;
        }
    }

    /**
     * Gets user data by login.
     *
     * @param string $login User login
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function getUserByLogin($login)
    {
        try {
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder->select('u.id', 'u.login', 'u.password')
                ->from('si_users', 'u')
                ->where('u.login = :login')
                ->setParameter(':login', $login, \PDO::PARAM_STR);

            return $queryBuilder->execute()->fetch();
        } catch (DBALException $exception) {
            return [];
        }
    }

    /**
     * Gets user roles by User ID.
     *
     * @param integer $userId User ID
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function getUserRoles($userId)
    {
        $roles = [];

        try {
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder->select('r.name')
                ->from('si_users', 'u')
                ->innerJoin('u', 'si_roles', 'r', 'u.role_id = r.id')
                ->where('u.id = :id')
                ->setParameter(':id', $userId, \PDO::PARAM_INT);
            $result = $queryBuilder->execute()->fetchAll();

            if ($result) {
                $roles = array_column($result, 'name');
            }

            return $roles;
        } catch (DBALException $exception) {
            return $roles;
        }
    }

    /**
     * Add new user
     * @param Application $app
     * @param array       $user User
     *
     * @return boolean Result
     */
    public function addUser(Application $app, $user)
    {
        if (isset($user['username']) && ctype_digit((string) $user['username'])) {
            throw new CustomUserMessageAuthenticationException(
                sprintf('Username "%s" already exists.', $user['username'])
            );
        } else {
            // add new record
            $user['password'] = $app['security.encoder.bcrypt']->encodePassword($user['password'], '');
            $user['role_id'] = 2;

            return $this->db->insert('si_users', $user);
        }
    }

    /**
     * Find for uniqueness.
     *
     * @param string          $name Element name
     * @param int|string|null $id   Element id
     *
     * @return array Result
     */
    public function findForUniqueness($name, $id = null)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('u.login = :login')
            ->setParameter(':login', $name, \PDO::PARAM_STR);
        if ($id) {
            $queryBuilder->andWhere('u.id <> :id')
                ->setParameter(':id', $id, \PDO::PARAM_INT);
        }

        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder
            ->select(
                'u.id',
                'u.login',
                'u.password',
                'u.role_id'
            )->from('si_users', 'u');
    }
}
