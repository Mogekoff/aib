<html>
<head>
    <title>Чертач</title>
    <link id="favicon" rel="icon" href="/img/favicon">
    <link rel="stylesheet" type="text/css" href="/css/home.css">
    <meta http-equiv="Content-Type" content="text/html" charset="utf-8">
    <meta name="description" content="Anonymous imageboard 'chertach'">
    <meta name="keywords" content="imageboard,anonymity">
    <meta name="author" content="anonym">
    <?php
        require_once("include/funcs.php");
    ?>
</head>
<body>
<div class="welcome">
    <h1>Черта.ч</h1>
    <h2>Зло пожаловать!</h2>
    <img width="100px" height="100px" src="/img/mascot">
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
                if($boards["uri"] == "fap" || $boards["uri"]=="bb") //игнорировать мутные разделы
                    continue;
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
    <p>Количество открытых досок на данный момент - <?php echo getBoardsCount(); ?>,
        количество тредов за всё время - <?php echo getThreadsCount(); ?>,
        количество оставленных анонами постов за всё время - <?php echo getPostsCount(); ?>,
        количество уникальных посетителей - <?php echo getAnonsCount() ?>.
    </p>
</footer>

</body>
</html>
