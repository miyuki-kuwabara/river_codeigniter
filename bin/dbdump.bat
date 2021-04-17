SET time_temp=%time: =0%
mysqldump -u root -p --add-drop-table --extended-insert --quote-names --single-transaction -h localhost la61037255-frograindrop > ..\application\sql\tables_%date:~0,4%%date:~5,2%%date:~8,2%%time_temp:~0,2%%time_temp:~3,2%%time_temp:~6,2%.dump
