SELECT
    nsc.chapter_id, ch.title, ch.intro, u.login as author, ch.created_at
FROM
    si_tree as nsp
    JOIN si_tree as nsc ON (nsc.id_left BETWEEN nsp.id_left + 1 AND nsp.id_right)
    left join si_chapters as ch on nsc.chapter_id=ch.id
    left join si_users as u on u.id=ch.author_id
WHERE
    nsp.id_left = 3
    AND NOT EXISTS (
        SELECT *
        FROM si_tree as ns
        WHERE
        ( ns.id_left BETWEEN nsp.id_left + 1 AND nsp.id_right )
        AND
        ( nsc.id_left BETWEEN ns.id_left + 1 AND ns.id_right )
    );