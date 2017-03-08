<?php

    require './lib/bootstrap.php';

    use AirTemplate\Builder;
    use AirTemplate\Loader\FilesystemLoader;

    $feed = simplexml_load_file('./data/medsamp2016_short.xml');
    //var_dump($feed->MedlineCitation);

    // create engine for page templates
    $loader = new FilesystemLoader('./templates/page');
    $builder = new Builder($loader);
    $page = $builder->build('*');

    // set a new template dir and build the pubmed rendering engine
    $loader->setDir('./templates/medline-citation');
    $pubmed = $builder->build('*');

    $builder = null;
    $loader = null;

    // render the page
    echo $page->render(
        'index_page',
        [
            'title' => 'Pubmed render test',
            'description' => 'Pubmed render test',
            'copyright' => '&copy 2016 Andreas Blaser',
            // embed the rendered pubmed citation
            'body' => $pubmed->render('medline-citation', $feed->MedlineCitation[0]),
            // the second item in the dataset has some missing fields
            //'body' => $pubmed->render('pubmed-citation', $feed->MedlineCitation[1]),
        ]
    );
