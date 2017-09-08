-- 1. pobranie listy elementów głównych (top-levelowych)

SELECT ns1.*
FROM si_tree as ns1
LEFT OUTER JOIN si_tree as ns2 
ON (ns1.id_left > ns2.id_left AND ns1.id_right < ns2.id_right)
WHERE ns2.id_left IS NULL;

-- zwrócić należy uwagę na fakt iż jeśli nasze drzewo ma tylko i wyłącznie 1 element top-levelowy to zapytanie można uprościć do:

SELECT * FROM si_tree WHERE id_left = 1;
-- ---------------------------------------------

-- 2. pobranie elementu bezpośrednio “nad” podanym elementem:

SELECT ns.*
FROM si_tree as ns
WHERE
    ID BETWEEN ns.id_left + 1 AND ns.id_right
ORDER BY ns.id_left DESC LIMIT 1;

-- jeśli zapytanie nic nie zwróci – znaczy to, że dany element był “top-levelowy”.
-- ----------------------------------------------------

-- 3. pobranie listy elementów bezpośrednio “pod” podanym elementem

SELECT
    nsc.*
FROM
    si_tree as nsp
    JOIN si_tree as nsc ON (nsc.id_left BETWEEN nsp.id_left + 1 AND nsp.id_right)
WHERE
    nsp.id_left = ID
    AND NOT EXISTS (
        SELECT *
        FROM si_tree as ns
        WHERE
        ( ns.id_left BETWEEN nsp.id_left + 1 AND nsp.id_right )
        AND
        ( nsc.id_left BETWEEN ns.id_left + 1 AND ns.id_right )
    );

-- 4. pobranie listy wszystkich elementów “nad” danym elementem (wylosowanym)

SELECT
    ns.*, ch.title
FROM
    si_tree as ns
    left join si_chapters as ch on ns.chapter_id=ch.id
WHERE
    ID BETWEEN ns.id_left + 1 AND ns.id_right;

-- ---------------------------------------

-- 5. pobranie listy wszystkich elementów “pod” danym elementem (wylosowanym)

SELECT
    nsc.*
FROM
    si_tree as nsp
    JOIN si_tree as nsc ON nsc.id_left BETWEEN nsp.id_left + 1 AND nsp.id_right
WHERE
    nsp.id_left = ID
-- --------------------------------