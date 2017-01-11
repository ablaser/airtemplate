<?php

    require './lib/bootstrap.php';

    use AirTemplate\FileTemplate;

    /**
     * List examples and wrap it in an article tag.
     *
     * @param array|object $example_data  Example data array
     * @param string       $title         Article title
     *
     * @return string      The rendered article
     */
    function listExamples($example_data, $title)
    {
        global $template;
        // Build example list
        // Separate items by a newline char (\n) and
        // apply htmlspecialchars to the description
        $example_list = $template->render(
            'index_list.tmpl',
            [
                'item_list' => $template->each(
                    'index_item.tmpl',
                    $example_data,
                    [
                        'name' => 'htmlspecialchars',
                        'desc' => 'htmlspecialchars'
                    ],
                    n
                )
            ]
        );
        // Return the list wrapped in an article tag
        return $template->render(
            'index_article.tmpl',
            [
                'article_title' => htmlspecialchars($title),
                'article_body' => $example_list
            ]
        );
    }


    // load list of examples from JSON file
    $example_data = json_decode(file_get_contents('./index_data.json'), true);

    // create a global template object and load templates
    $template = new FileTemplate;
    $template->loadTemplates('index_*.tmpl', './templates');

    // render the examples menu page
    echo $template->render(
        'index_page.tmpl',
        [
            'title' => htmlspecialchars('AirTemplate Examples'),
            'description' => htmlspecialchars('Some examples demonstrating the use of AirTemplate Templates.'),
            'copyright' => '&copy; 2016 Andreas Blaser',
            'body' => listExamples($example_data, 'AirTemplate Examples'),
        ]
    );

?>