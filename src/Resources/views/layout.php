<?php
/**
 * @var \League\Plates\Template\Template $this
 * @var \SlashTrace\Template\ResourceLoader $resourceLoader
 * @var string $pageTitle
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $this->escape($pageTitle); ?></title>

    <?= $resourceLoader->stylesheet("bootstrap.css"); ?>
    <?= $resourceLoader->stylesheet("fontello.css"); ?>
    <?= $resourceLoader->stylesheet("main.css"); ?>
    <?= $resourceLoader->stylesheet("themes/default.css"); ?>
</head>
<body>
<div id="page">
    <?= $this->section("content"); ?>
</div>
<?= $resourceLoader->script("prettify.js"); ?>
<?= $resourceLoader->script("main.js"); ?>
</body>
</html>