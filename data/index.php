<html>
<head>
    <title>Чертач</title>
    <link id="favicon" rel="icon" href="/img/favicon">
    <link rel="stylesheet" type="text/css" href="/css/home.css">
    <meta http-equiv="Content-Type" content="text/html" charset="utf-8">
    <meta name="description" content="Anonymous imageboard 'chertach'">
    <meta name="keywords" content="imageboard,anonymity">
    <meta name="author" content="anonym">
</head>
<body>
<div class="welcome">
    <h1>Черта.ч</h1>
    <h2>Зло пожаловать!</h2>
    <img width="200px" height="200px" src="/img/logos/<?php echo rand(1,38); ?>.jpg">
</div>

<header class="block">
    <h4>О сайте:</h4>
    <hr>
    <p><span style="font-weight:bold">Черта.ч</span> - это анонимный имиджборд форум, созданный для свободного общения.
        Здесь Вы можете задать вопрос или открыть дискуссию на любую интересующую вас тему.
        Мы не ведём никаких логов о посетителях, так что ваша анонимность под защитой.
        Сервер форума спрятан так глубоко под землёй, что до него не доберётся ни один майор.
        Параноики могут быть спокойны.
    </p>
</header>

<section class="block">
    <h4>Список досок:</h4>
    <hr>
    <table class="boards-list">
        <tr>
            <th>Доска</th>
            <th>Название</th>
            <th>Описание</th>
        </tr>
        <?php
            $query = getAllBoards();
            while($boards = pg_fetch_array($query, null, PGSQL_ASSOC)) {
                echo
                    "<tr>
                        <td><a href='/".$boards["uri"]."'>".$boards["uri"]."</a></td>
                        <td>".$boards["name"]."</td>
                        <td>".$boards["description"]."</td>
                    </tr>";
            }
        ?>
    </table>
</section>

<footer class="block">
    <h4>Статистика:</h4>
    <hr>
    <p>Количество открытых досок на данный момент - <b><?php echo getBoardsCount(); ?></b>,
        количество тредов за всё время - <b><?php echo getThreadsCount(); ?></b>,
        количество оставленных анонами постов за всё время - <b><?php echo getPostsCount(); ?></b>,
        количество уникальных посетителей - <b><?php echo getAnonsCount() ?></b>. 
        А на плаву мы уже <b><?php echo date_diff(new DateTime(), new DateTime('2020-07-06 00:00:00'))->days; ?></b> дня.
    </p>
</footer>

</body>
</html>
