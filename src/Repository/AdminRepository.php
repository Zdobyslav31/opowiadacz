<?php
/**
 * Chapter repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Silex\Application;
use Utils\Paginator;

/**
 * Class AdminRepository.
 *
 * @package Repository
 */
class AdminRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * Number of items per page.
     *
     * const int NUM_ITEMS
     */
    const NUM_ITEMS = 15;

    /**
     * ChapterRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }


    /**
     * Remove record.
     *
     * @param array $user User
     *
     * @return boolean Result
     */
    public function delete($user)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->update('si_chapters', 'ch')
            ->set('ch.author_id', 0)
            ->where('ch.author_id = :id')
            ->setParameter(':id', $user['id'], \PDO::PARAM_INT);
        $queryBuilder->execute();

        return $this->db->delete('si_users', ['id' => $user['id']]);
    }

    /**
     * Change password.
     *
     * @param int                $userId User id
     * @param array              $form   Form
     * @param \Silex\Application $app    Silex application
     *
     * @return boolean Result
     */
    public function changePassword($userId, $form, Application $app)
    {
        $passwordPlain = $form['password'];
        $password = $app['security.encoder.bcrypt']->encodePassword($passwordPlain, '');
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->update('si_users', 'u')
            ->set('u.password', ':password')
            ->where('u.id = :id')
            ->setParameter(':id', $userId, \PDO::PARAM_INT)
            ->setParameter(':password', $password, \PDO::PARAM_STR);

        return $queryBuilder->execute();
    }


    /**
     * Give admin's permissions.
     *
     * @param array $user User
     *
     * @return boolean Result
     */
    public function changePermissions($user)
    {
        if ($user['role_id'] == 2) {
            $newRole = 1;
        } else {
            $newRole = 2;
        }
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->update('si_users', 'u')
            ->set('u.role_id', ':new_role')
            ->where('u.id = :id')
            ->setParameter(':id', $user['id'], \PDO::PARAM_INT)
            ->setParameter(':new_role', $newRole, \PDO::PARAM_INT);

        return $queryBuilder->execute();
    }

    /**
     * Fetch all records.
     *
     * @param int $page
     *
     * @return array Result
     */
    public function findUsersWithChapters($page)
    {
        $paginator = $this->findAllPaginated($page);
        foreach ($paginator['data'] as &$user) {
            $user['chapters'] = $this->findLinkedChapters($user['id']);
        }

        return $paginator;
    }

    /**
     * Get users paginated.
     *
     * @param int $page Current page number
     *
     * @return array Result
     */
    public function findAllPaginated($page = 1)
    {
        $countQueryBuilder = $this->queryAll()
            ->select('COUNT(DISTINCT u.id) AS total_results')
            ->setMaxResults(1);

        $paginator = new Paginator($this->queryAll(), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(self::NUM_ITEMS);

        return $paginator->getCurrentPageResults();
    }

    /**
     * Find one record.
     *
     * @param string $id Element id
     *
     * @return array|mixed Result
     */
    public function findOneById($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder
            ->where('u.id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();
        $result['chapters'] = $this->findLinkedChapters($id);

        return !$result ? [] : $result;
    }

    /**
     * Finds linked tags Ids.
     *
     * @param int $bookmarkId Bookmark Id
     *
     * @return array Result
     */
    protected function findLinkedChapters($userId)
    {
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('ch.id', 'ch.title')
            ->from('si_chapters', 'ch')
            ->where('ch.author_id = :user_id')
            ->orderBy('created_at', 'DESC')
            ->setParameter(':user_id', $userId, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();

        return isset($result) ? $result : [];
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
                'u.role_id',
                'u.password'
            )->from('si_users', 'u')
            ->where('u.id>0')
            ->orderBy('u.id', 'DESC');
    }
}
