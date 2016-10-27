<?php

if (!file_exists('config.php')) {
    die('config.php not found.');
}

require_once 'config.php';
require_once 'src/Autoloader.php';

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <title><?php echo $config['title']; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"
            integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
            crossorigin="anonymous"></script>
</head>
<body>
<?php echo Map::generate(); ?>
</body>
</html>
