<?php

    require './lib/bootstrap.php';

    use AirTemplate\Builder;
    use AirTemplate\Loader\FilesystemLoader;

    $feed = simplexml_load_file('./data/feedforall.com-sample-feed.xml');

    // create engine for page templates
    $loader = new FilesystemLoader('./templates/page');
    $builder = new Builder($loader);
    $page = $builder->build('*');

    // set a new template directory and build render engine
    $loader->setDir('./templates/rss');
    $rss = $builder->build('*');

    $builder = null;
    $loader = null;

    // render the page
    echo $page->render(
        'index_page',
        [
            'title' => 'RSS feed render test',
            'description' => 'Product render test',
            'copyright' => '&copy 2016 Andreas Blaser',
            'body' => $rss->render('rss', $feed->channel),
        ]
    );

?>