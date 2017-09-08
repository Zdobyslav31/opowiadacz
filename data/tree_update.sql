select * from si_tree;
select * from si_chapters;

start transaction;
update si_tree set id_right = id_right + 2 where id_right >= 5 order by id_right DESC;
update si_tree set id_left = id_left + 2 where id_left >= 5 order by id_left DESC;
insert into si_tree (id_left, id_right, chapter_id) values (5, 5+1, 32);
commit;

start transaction;
update si_tree set id_right = id_right + 2 where id_right >= 12 order by id_right DESC;
update si_tree set id_left = id_left + 2 where id_left >= 12 order by id_left DESC;
insert into si_tree (id_left, id_right, chapter_id) values (12, 12+1, 29);
commit;
rollback;

start transaction;
delete from si_tree where id_left=5;
update si_tree set id_left = id_left - 2 where id_left >= 5 order by id_left ASC;
update si_tree set id_right = id_right - 2 where id_right >= 5 order by id_right ASC;
commit;
