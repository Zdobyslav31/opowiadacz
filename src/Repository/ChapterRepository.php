<?php
/**
 * Chapter repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Silex\Application;
use Utils\Paginator;
use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Class ChapterRepository.
 *
 * @package Repository
 */
class ChapterRepository
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
    const NUM_ITEMS = 5;

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
     * @param array $chapter Chapter
     *
     * @return boolean Result
     */
    public function delete($chapter)
    {
        return $this->db->delete('si_chapters', ['id' => $chapter['id']]);
    }

    /**
     * Fetch all records.
     *
     * @return array Result
     */
    public function findAll()
    {
        $queryBuilder = $this->queryAll();

        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * Get records paginated.
     *
     * @param int $page Current page number
     *
     * @return array Result
     */
    public function findAllPaginated($page = 1)
    {
        $countQueryBuilder = $this->queryAll()
            ->select('COUNT(DISTINCT ch.id) AS total_results')
            ->setMaxResults(1);

        $paginator = new Paginator($this->queryAll(), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(self::NUM_ITEMS);

        return $paginator->getCurrentPageResults();
    }

    /**
     * Find records by user.
     *
     * @param \Silex\Application $app  Silex application
     * @param int                $page Page number
     *
     * @return array|mixed Result
     */
    public function findByUserPaginated(Application $app, $page = 1)
    {
        $userId = $app['user']->getId();
        $countQueryBuilder = $this->queryAll()
            ->select('COUNT(DISTINCT ch.id) AS total_results')
            ->setMaxResults(1)
            ->where('ch.author_id = :user')
            ->setParameter(':user', $userId, \PDO::PARAM_INT);

        $paginator = new Paginator(
            $this
            ->queryAll()
            ->where('ch.author_id = :user')
            ->setParameter(':user', $userId, \PDO::PARAM_INT),
            $countQueryBuilder
        );
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
            ->where('ch.id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * Save record.
     *
     * @param array              $chapter Chapter
     * @param \Silex\Application $app     Silex application
     *
     * @return boolean Result
     */
    public function save($chapter, Application $app)
    {
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $config->set('HTML.Allowed', 'strong,em,u,br,p[style|align],span[style],hr,h1[align],h2[align],h3[align],h4[align],h5[align],h6[align],small');
        $currentDateTime = new \DateTime();
        $chapter['content'] = $purifier->purify($chapter['content']);
//        $chapter['content'] = $purifier->purify($chapter['content'], '<strong><em><u><br><p><span><hr><h1><h2><h3><h4><h5><h6><small></small>');

        $chapter['modified_at'] = $currentDateTime->format('Y-m-d H:i:s');
        if (isset($chapter['id']) && ctype_digit((string) $chapter['id'])) {
            // update record
            $id = $chapter['id'];
            unset($chapter['id']);
            unset($chapter['author']);
            unset($chapter['author_id']);
            unset($chapter['has_children']);

            return $this->db->update('si_chapters', $chapter, ['id' => $id]);
        } else {
            // add new record
            $chapter['author_id'] = $app['user']->getId();
            $chapter['created_at'] = $currentDateTime->format('Y-m-d H:i:s');
            $this->db->insert('si_chapters', $chapter);

            return $this->db->lastInsertId();
        }
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
                'ch.id',
                'ch.created_at',
                'ch.title',
                'u.login as author',
                'ch.author_id',
                'ch.intro',
                'ch.content',
                'ch.summary',
                '(ns.id_left != ns.id_right-1) AS has_children'
            )->from('si_chapters', 'ch')
            ->leftJoin('ch', 'si_users', 'u', 'u.id=ch.author_id')
            ->leftJoin('ch', 'si_tree', 'ns', 'ns.chapter_id=ch.id')
            ->orderBy('created_at', 'DESC');
    }
}
