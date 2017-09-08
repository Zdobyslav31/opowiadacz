<?php
/**
 * Created by PhpStorm.
 * User: osboxes
 * Date: 03/09/17
 * Time: 21:02
 */

namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class TreeRepository.
 *
 * @package Repository
 */
class TreeRepository
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
     * Add element to the tree.
     *
     * @param int $idParent Element id_parent.
     *
     * @param int $idNew    Element id_new
     */
    public function addElement($idParent, $idNew)
    {
        $rightId = $this->getRightId($idParent);
        $this->db->beginTransaction();
        try {
            ;
            $query1 = "UPDATE si_tree SET id_right = id_right + 2 WHERE id_right >= ? ORDER BY id_right DESC";
            $q1 = $this->db->prepare($query1);
            $q1->bindValue(1, $rightId, \PDO::PARAM_INT);
            $q1->execute();

            $query2 = "UPDATE si_tree SET id_left = id_left + 2 WHERE id_left >= ? ORDER BY id_left DESC";
            $q2 = $this->db->prepare($query2);
            $q2->bindValue(1, $rightId, \PDO::PARAM_INT);
            $q2->execute();
//
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder
                ->insert('si_tree')
                ->values(
                    array(
                        'id_left' => ':id',
                        'id_right' => ':id+1',
                        'chapter_id' => ':chapter_id',
                    )
                )
                ->setParameter(':id', $rightId, \PDO::PARAM_INT)
                ->setParameter(':chapter_id', $idNew, \PDO::PARAM_INT);
            $queryBuilder->execute();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return;
    }

    /**
     * Remove element from the tree.
     *
     * @param int $id Element id.
     */
    public function removeElement($id)
    {
        $leftId = $this->getLeftId($id);
        $this->db->beginTransaction();
        try {
            ;
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder
                ->delete('si_tree')
                ->where('id_left=:id')
                ->setParameter(':id', $leftId, \PDO::PARAM_INT);
            $queryBuilder->execute();

            $query1 = "UPDATE si_tree SET id_left = id_left - 2 WHERE id_left >= ? ORDER BY id_left ASC";
            $q1 = $this->db->prepare($query1);
            $q1->bindValue(1, $leftId, \PDO::PARAM_INT);
            $q1->execute();

            $query2 = "UPDATE si_tree SET id_right = id_right - 2 WHERE id_right >= ? ORDER BY id_right ASC";
            $q2 = $this->db->prepare($query2);
            $q2->bindValue(1, $leftId, \PDO::PARAM_INT);
            $q2->execute();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return;
    }


    /**
     * Find parent id.
     *
     * @param string $id Element id
     *
     * @return array|mixed Result
     */
    public function findParentId($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $leftId = $this->getLeftId($id);

        $queryBuilder
            ->select('ns.chapter_id')
            ->from('si_tree', 'ns')
            ->where(':id between ns.id_left + 1 AND ns.id_right')
            ->orderBy('ns.id_left', 'DESC')
            ->setMaxResults(1)
            ->setParameter(':id', $leftId, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetch();

        return !$result ? 0 : $result['chapter_id'];
    }

    /**
     * Find all ancestors' ond current object's summaries.
     *
     * @param string $id Element id
     *
     * @return array|mixed Result
     */
    public function findCurrentPlotSummary($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $leftId = $this->getLeftId($id);

        $queryBuilder
            ->select('ns.chapter_id', 'ch.title', 'ch.summary')
            ->from('si_tree', 'ns')
            ->leftJoin('ns', 'si_chapters', 'ch', 'ns.chapter_id=ch.id')
            ->where(':id between ns.id_left AND ns.id_right')
            ->orderBy('ns.id_left', 'ASC')
            ->setParameter(':id', $leftId, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetchAll();

        return !$result ? [] : $result;
    }

    /**
     * Find all children's intro.
     *
     * @param string $id Element id
     *
     * @return array|mixed Result
     */
    public function findChildren($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $leftId = $this->getLeftId($id);

        $queryBuilder
            ->select('nsc.chapter_id', 'ch.title', 'ch.intro', 'u.login as author', 'ch.created_at')
            ->from('si_tree', 'nsp')
            ->join('nsp', 'si_tree', 'nsc', '(nsc.id_left BETWEEN nsp.id_left + 1 AND nsp.id_right)')
            ->leftJoin('nsc', 'si_chapters', 'ch', 'nsc.chapter_id=ch.id')
            ->leftJoin('ch', 'si_users', 'u', 'u.id=ch.author_id')
            ->where('nsp.id_left = :id
    AND NOT EXISTS (
        SELECT *
        FROM si_tree as ns
        WHERE
        ( ns.id_left BETWEEN nsp.id_left + 1 AND nsp.id_right )
        AND
        ( nsc.id_left BETWEEN ns.id_left + 1 AND ns.id_right )
    )')
            ->setParameter(':id', $leftId, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetchAll();

        return !$result ? [] : $result;
    }

    /**
     * Convert chapter's id to tree left_id.
     *
     * @param string $id Element id
     *
     * @return string
     */
    private function getLeftId($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder
            ->select('ns.id_left')
            ->from('si_tree', 'ns')
            ->where('ns.chapter_id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetch();

        return !$result ? 0 : $result['id_left'];
    }

    /**
     * Convert chapter's id to tree right_id.
     *
     * @param string $id Element id
     *
     * @return string
     */
    private function getRightId($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder
            ->select('ns.id_right')
            ->from('si_tree', 'ns')
            ->where('ns.chapter_id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);

        $result = $queryBuilder->execute()->fetch();

        return !$result ? 0 : $result['id_right'];
    }
}
