id: 32
name: MarkdownMimeType
category: c_content
plugincode: "/**\n * MarkdownMimeType\n *\n * Retain original Markdown markup by setting the proper MIME type for a\n * Markdown resource. Set the MIME type back to HTML when viewing the resource\n * in the browser, to prevent the page from being downloaded as file.\n *\n * In addition, HtmlPageDom is used to fix image URLs and prevent them from\n * overflowing their container.\n *\n * For rendering Markdown as HTML, install the Markdown extra from modstore.pro:\n * https://modstore.pro/packages/content/markdown\n *\n * Process markdown in your template like this:\n *\n * [[*content:Markdown]]\n */\n\n$corePath = $modx->getOption('htmlpagedom.core_path', null, $modx->getOption('core_path') . 'components/htmlpagedom/');\n\nif (!class_exists('\\Wa72\\HtmlPageDom\\HtmlPageCrawler')) {\n    require $corePath . 'vendor/autoload.php';\n}\n\nuse \\Wa72\\HtmlPageDom\\HtmlPageCrawler;\n\nswitch ($modx->event->name) {\n    // Set content type to Markdown when resource has a markdown template\n    case 'OnBeforeDocFormSave':\n        $template = $modx->getObject('modTemplate', array('id'=>$resource->get('template')));\n\n        if (!is_object($template)) {\n            break;\n        }\n\n        // Any template with 'Markdown' in its name qualifies\n        // Note to self: you need to revert the content type manually if you assign a non-markdown template again\n        if (stripos($template->get('templatename'), 'markdown') !== false) {\n            $resource->set('contentType', 'text/x-markdown');\n            $resource->set('content_type', '11');\n\n            // Also disable any active text editor\n            $resource->set('richtext', 0);\n        }\n\n        break;\n\n    // Use HTML mime type when viewed as a web page\n    // Inspired by: https://github.com/GoldCoastMedia/modx-xhtml-mime-switch\n    case 'OnWebPagePrerender':\n        $resource = &$modx->resource;\n\n        // Header content types\n        $header = (object) array(\n            'markdown'  => 'text/x-markdown',\n            'html' => 'text/html',\n        );\n\n        // Get the document type\n        $markdown = ($resource->get('contentType') === $header->markdown) ? true : false;\n\n        // Switch back to HTML\n        if ($markdown) {\n            $resource->ContentType->set('mime_type', $header->html);\n        } else {\n            break;\n        }\n\n        // Process output with HtmlPageDom\n        $output = &$resource->_output;\n        $dom = new HtmlPageCrawler($output);\n\n        // Fix image URLs and display size\n        $dom->filter('img')\n            ->each(function (HtmlPageCrawler $image) {\n                $src = $image->getAttribute('src');\n                $image\n                    ->setAttribute('src', 'notes/' . $src)\n                    ->addClass('ui image')\n                ;\n            })\n        ;\n\n        $output = $dom->saveHTML();\n\n        break;\n}"
properties: 'a:0:{}'
content: "/**\n * MarkdownMimeType\n *\n * Retain original Markdown markup by setting the proper MIME type for a\n * Markdown resource. Set the MIME type back to HTML when viewing the resource\n * in the browser, to prevent the page from being downloaded as file.\n *\n * In addition, HtmlPageDom is used to fix image URLs and prevent them from\n * overflowing their container.\n *\n * For rendering Markdown as HTML, install the Markdown extra from modstore.pro:\n * https://modstore.pro/packages/content/markdown\n *\n * Process markdown in your template like this:\n *\n * [[*content:Markdown]]\n */\n\n$corePath = $modx->getOption('htmlpagedom.core_path', null, $modx->getOption('core_path') . 'components/htmlpagedom/');\n\nif (!class_exists('\\Wa72\\HtmlPageDom\\HtmlPageCrawler')) {\n    require $corePath . 'vendor/autoload.php';\n}\n\nuse \\Wa72\\HtmlPageDom\\HtmlPageCrawler;\n\nswitch ($modx->event->name) {\n    // Set content type to Markdown when resource has a markdown template\n    case 'OnBeforeDocFormSave':\n        $template = $modx->getObject('modTemplate', array('id'=>$resource->get('template')));\n\n        if (!is_object($template)) {\n            break;\n        }\n\n        // Any template with 'Markdown' in its name qualifies\n        // Note to self: you need to revert the content type manually if you assign a non-markdown template again\n        if (stripos($template->get('templatename'), 'markdown') !== false) {\n            $resource->set('contentType', 'text/x-markdown');\n            $resource->set('content_type', '11');\n\n            // Also disable any active text editor\n            $resource->set('richtext', 0);\n        }\n\n        break;\n\n    // Use HTML mime type when viewed as a web page\n    // Inspired by: https://github.com/GoldCoastMedia/modx-xhtml-mime-switch\n    case 'OnWebPagePrerender':\n        $resource = &$modx->resource;\n\n        // Header content types\n        $header = (object) array(\n            'markdown'  => 'text/x-markdown',\n            'html' => 'text/html',\n        );\n\n        // Get the document type\n        $markdown = ($resource->get('contentType') === $header->markdown) ? true : false;\n\n        // Switch back to HTML\n        if ($markdown) {\n            $resource->ContentType->set('mime_type', $header->html);\n        } else {\n            break;\n        }\n\n        // Process output with HtmlPageDom\n        $output = &$resource->_output;\n        $dom = new HtmlPageCrawler($output);\n\n        // Fix image URLs and display size\n        $dom->filter('img')\n            ->each(function (HtmlPageCrawler $image) {\n                $src = $image->getAttribute('src');\n                $image\n                    ->setAttribute('src', 'notes/' . $src)\n                    ->addClass('ui image')\n                ;\n            })\n        ;\n\n        $output = $dom->saveHTML();\n\n        break;\n}"

-----


/**
 * MarkdownMimeType
 *
 * Retain original Markdown markup by setting the proper MIME type for a
 * Markdown resource. Set the MIME type back to HTML when viewing the resource
 * in the browser, to prevent the page from being downloaded as file.
 *
 * In addition, HtmlPageDom is used to fix image URLs and prevent them from
 * overflowing their container.
 *
 * For rendering Markdown as HTML, install the Markdown extra from modstore.pro:
 * https://modstore.pro/packages/content/markdown
 *
 * Process markdown in your template like this:
 *
 * [[*content:Markdown]]
 */

$corePath = $modx->getOption('htmlpagedom.core_path', null, $modx->getOption('core_path') . 'components/htmlpagedom/');

if (!class_exists('\Wa72\HtmlPageDom\HtmlPageCrawler')) {
    require $corePath . 'vendor/autoload.php';
}

use \Wa72\HtmlPageDom\HtmlPageCrawler;

switch ($modx->event->name) {
    // Set content type to Markdown when resource has a markdown template
    case 'OnBeforeDocFormSave':
        $template = $modx->getObject('modTemplate', array('id'=>$resource->get('template')));

        if (!is_object($template)) {
            break;
        }

        // Any template with 'Markdown' in its name qualifies
        // Note to self: you need to revert the content type manually if you assign a non-markdown template again
        if (stripos($template->get('templatename'), 'markdown') !== false) {
            $resource->set('contentType', 'text/x-markdown');
            $resource->set('content_type', '11');

            // Also disable any active text editor
            $resource->set('richtext', 0);
        }

        break;

    // Use HTML mime type when viewed as a web page
    // Inspired by: https://github.com/GoldCoastMedia/modx-xhtml-mime-switch
    case 'OnWebPagePrerender':
        $resource = &$modx->resource;

        // Header content types
        $header = (object) array(
            'markdown'  => 'text/x-markdown',
            'html' => 'text/html',
        );

        // Get the document type
        $markdown = ($resource->get('contentType') === $header->markdown) ? true : false;

        // Switch back to HTML
        if ($markdown) {
            $resource->ContentType->set('mime_type', $header->html);
        } else {
            break;
        }

        // Process output with HtmlPageDom
        $output = &$resource->_output;
        $dom = new HtmlPageCrawler($output);

        // Fix image URLs and display size
        $dom->filter('img')
            ->each(function (HtmlPageCrawler $image) {
                $src = $image->getAttribute('src');
                $image
                    ->setAttribute('src', 'notes/' . $src)
                    ->addClass('ui image')
                ;
            })
        ;

        $output = $dom->saveHTML();

        break;
}