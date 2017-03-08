<?php
    require './lib/bootstrap.php';
    require '../vendor/michelf/php-markdown/Michelf/Markdown.inc.php';

    use AirTemplate\Builder;
    use AirTemplate\Loader\FilesystemLoader;

    // load page metadata from JSON file
    $pageData = json_decode(file_get_contents('./_data.json'));
    // Add markdown content
    $pageData->index->body = file_get_contents('./index.md');

    // create page render engine
    $builder = new Builder(new FilesystemLoader('./templates/page'));
    $page = $builder->build('*');
    $builder = null;

    // render the examples menu page
    echo $page->render(
        $pageData->index->_layout,
        $pageData->index
    );
