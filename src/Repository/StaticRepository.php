<?php
/**
 * Chapter repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Silex\Application;

/**
 * Class ChapterRepository.
 *
 * @package Repository
 */
class StaticRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

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
     * @param int    $textId   Text id
     * @param string $language Language
     *
     * @return string Result
     */
    public function getText($textId, $language)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder
            ->select('content')
            ->from('si_static_texts')
            ->where('id = :id AND language = :language')
            ->setParameter(':id', $textId, \PDO::PARAM_INT)
            ->setParameter(':language', $language, \PDO::PARAM_STR)
            ->execute()->fetch();
    }
}
