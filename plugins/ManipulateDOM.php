id: 39
name: ManipulateDOM
description: 'Manipulate DOM elements with HtmlPageDom. Yes, that''s exactly what jQuery does... But now we can do it server side, before the page is rendered. Much faster and more reliable.'
category: c_content
plugincode: "/**\n * ManipulateDOM plugin\n *\n * This plugin utilizes HtmlPageDom, a page crawler that can manipulate DOM\n * elements for us. Yes, that is exactly what jQuery does... But now we can do\n * it server side, before the page is rendered. Much faster and more reliable.\n *\n * @var modX $modx\n * @var array $scriptProperties\n * @package romanesco\n */\n\nif (!class_exists(\\Wa72\\HtmlPageDom\\HtmlPageCrawler::class)) {\n    $modx->log(modX::LOG_LEVEL_ERROR, '[HtmlPageDom] Class not found!');\n    return;\n}\n\nuse \\Wa72\\HtmlPageDom\\HtmlPageCrawler;\n\nswitch ($modx->event->name) {\n    case 'OnWebPagePrerender':\n\n        // Check if content type is text/html\n        if (!in_array($modx->resource->get('content_type'), [1,11])) {\n            break;\n        }\n\n        // Get processed output of resource\n        $output = &$modx->resource->_output;\n\n        // Feed output to HtmlPageDom\n        $dom = new HtmlPageCrawler($output);\n\n        // Add non-white class to body if custom background is set\n        try {\n            if ($modx->getObject('cgSetting', array('key' => 'theme_page_background_color'))->get('value') !== 'ffffff') {\n                $dom->filter('body')->addClass('non-white');\n            }\n        }\n        catch (Error $e) {\n            $modx->log(modX::LOG_LEVEL_ERROR, $e);\n        }\n\n        // Read inverted parameter from URL (for testing purposes)\n        $invertLayouts = $_GET['inverted'] ?? 0;\n        if ($invertLayouts) {\n            $dom->filter('.ui.menu')\n                ->addClass('inverted')\n            ;\n            $dom->filter('.vertical.stripe.segment.white')\n                ->removeClass('white')\n                ->addClass('inverted')\n            ;\n            $dom->filter('.vertical.backyard.segment.secondary')\n                ->removeClass('secondary')\n                ->addClass('inverted black')\n            ;\n        }\n\n        // Add header class to content headers without class name\n        $dom->filter('h1:not(.header)')->addClass('ui header');\n        $dom->filter('h2:not(.header)')->addClass('ui header');\n        $dom->filter('h3:not(.header)')->addClass('ui header');\n        $dom->filter('h4:not(.header)')->addClass('ui header');\n        $dom->filter('h5:not(.header)')->addClass('ui header');\n\n        // Inject inverted classes to elements inside inverted segments\n        $dom->filter('.inverted.segment')\n            ->each(function (HtmlPageCrawler $segment) {\n\n                // Define elements that need to receive the inverted class\n                $elements = array(\n                    '.header',\n                    '.grid',\n                    'a:not(.button)',\n                    '.button:not(.primary):not(.secondary)',\n                    '.subtitle',\n                    '.lead',\n                    '.list',\n                    '.quote',\n                    '.divider:not(.hidden)',\n                    '.accordion:not(.styled)',\n                    '.text.menu',\n                    '.secondary.menu',\n                    '.basic.form',\n                    '.table',\n                    '.steps',\n                );\n\n                // Revert inverted styling inside these nested elements\n                $exceptions = array(\n                    '.segment:not(.inverted):not(.transparent)',\n                    '.card',\n                    '.message',\n                    '.accordion:not(.inverted)',\n                    '.popup:not(.inverted)',\n                    '.tabbed.menu',\n                    '.form:not(.basic)',\n                    '.leaflet-container',\n                );\n\n                // Prevent elements from having the same color as their parent background\n                if ($segment->hasClass('primary-color') || $segment->hasClass('secondary-color') || $segment->hasClass('secondary')) {\n                    $segment\n                        ->filter('.primary.button')\n                        ->addClass('white inverted')\n                    ;\n                    $segment\n                        ->filter('.secondary.button')\n                        ->removeClass('secondary')\n                        ->addClass('inverted')\n                    ;\n                    $segment\n                        ->filter('.bottom.attached.primary.button')\n                        ->removeClass('primary')\n                        ->addClass('secondary')\n                    ;\n                }\n\n                // Elements\n                foreach ($elements as $element) {\n                    $segment\n                        ->filter($element)\n                        ->addClass('inverted')\n                    ;\n                }\n\n                // Exceptions\n                foreach ($exceptions as $exception) {\n                    $segment\n                        ->filter($exception)\n                        ->each(function(HtmlPageCrawler $node) {\n                            $node\n                                ->filter('.inverted')\n                                ->removeClass('inverted')\n                            ;\n                            $node\n                                ->filter('.ui.white.button')\n                                ->removeClass('white')\n                            ;\n                        })\n                    ;\n                }\n            })\n        ;\n\n        // Remove rows from grids that have a reversed column order on mobile\n        $dom->filter('.ui.reversed.grid > .row')->unwrapInner();\n\n        // If grids are stackable on tablet, also hide (or show) designated mobile elements\n        $dom->filter('.ui[class*=\"stackable on tablet\"].grid [class*=\"mobile hidden\"]')\n            ->removeClass('mobile')\n            ->removeClass('hidden')\n            ->addClass('tablet or lower hidden')\n        ;\n        $dom->filter('.ui[class*=\"stackable on tablet\"].grid [class*=\"mobile only\"]')\n            ->addClass('tablet only')\n        ;\n\n        // Responsive image sizes might be incorrect in responsive grids\n        $dom->filter('.ui.stackable.grid, .ui.doubling.grid, .ui.stackable.cards')\n            ->each(function(HtmlPageCrawler $grid) {\n                $targetImg = '.row > .column > .ui.image > img, .column > .ui.image > img';\n                if ($grid->matches('.cards')) {\n                    $targetImg = '.card > .image > img';\n                }\n\n                // Tag images in stackable on tablet and two column doubling grids\n                if ($grid->matches('[class*=\"stackable on tablet\"]') || $grid->matches('[class*=\"two column\"].doubling')) {\n                    $grid->children($targetImg)->addClass('tablet-expand-full');\n                }\n                // Do the same for doubling grids with more than two columns\n                else if ($grid->matches('.doubling:not([class*=\"two column\"]):not([class*=\"equal width\"])')) {\n                    $grid->children($targetImg)->addClass('tablet-expand-half');\n\n                    if ($grid->matches('.doubling:not(.stackable)')) {\n                        $grid->children($targetImg)->addClass('mobile-expand-half');\n                    }\n                }\n\n                // Only target direct descendants\n                $grid->children($targetImg)\n                    ->each(function(HtmlPageCrawler $img) {\n                        $dataSizes = $img->getAttribute('data-sizes');\n                        $sizes = $dataSizes ?? $img->getAttribute('sizes');\n\n                        if (!$sizes) return;\n\n                        // If lazy load is enabled, sizes are stored in data-sizes\n                        $attribute = 'sizes';\n                        if ($dataSizes) $attribute = 'data-sizes';\n\n                        // Set mobile breakpoints to 100vw, because stacked means full width\n                        $stackedSizes = preg_replace('/\\(min-width: 360px\\).+/','(min-width: 360px) 100vw,', $sizes);\n                        $stackedSizes = preg_replace('/\\(max-width: 359px\\).+/','(max-width: 359px) 100vw', $stackedSizes);\n\n                        // Set optional sizes, if indicated\n                        if ($img->matches('.tablet-expand-full')) {\n                            $stackedSizes = preg_replace('/\\(min-width: 768px\\).+/','(min-width: 768px) 100vw,', $stackedSizes);\n                        }\n                        if ($img->matches('.tablet-expand-half')) {\n                            $stackedSizes = preg_replace('/\\(min-width: 768px\\).+/','(min-width: 768px) 50vw,', $stackedSizes);\n                        }\n                        if ($img->matches('.mobile-expand-half')) {\n                            $stackedSizes = preg_replace('/\\(min-width: 360px\\).+/','(min-width: 360px) 50vw,', $stackedSizes);\n                            $stackedSizes = preg_replace('/\\(max-width: 359px\\).+/','(max-width: 359px) 50vw', $stackedSizes);\n                        }\n\n                        $img->setAttribute($attribute, $stackedSizes);\n                    })\n                ;\n            })\n        ;\n\n        // Add class to empty grid columns\n        $dom->filter('.ui.grid .column')\n            ->each(function(HtmlPageCrawler $column) {\n                if($column->getInnerHtml() === '') {\n                    $column->addClass('empty');\n                }\n            })\n        ;\n\n        // Display bullets above list items in centered lists\n        $dom->filter('.ui.center.aligned')\n            ->each(function(HtmlPageCrawler $container) {\n                $container->filter('.ui.list')->addClass('vertical');\n                $container->filter('.aligned:not(.center) .ui.vertical.list')->removeClass('vertical');\n            })\n        ;\n\n        // Make regular divider headers smaller\n        $dom->filter('span.ui.divider.header')\n            ->addClass('tiny')\n        ;\n\n        // Transform regular tables into SUI tables\n        $dom->filter('table:not(.ui.table)')\n            ->addClass('ui table')\n        ;\n\n        // Apply Swiper classes to appropriate slide elements\n        $dom->filter('.swiper')\n            ->each(function (HtmlPageCrawler $slider) {\n                $slider\n                    ->children('.nested.overview')\n                    ->removeClass('stackable')\n                    ->removeClass('doubling')\n                    ->addClass('swiper-wrapper')\n                ;\n                $slider\n                    ->children('.gallery')\n                    ->addClass('swiper-wrapper')\n                ;\n                $slider\n                    ->children('.cards')\n                    ->addClass('swiper-wrapper')\n                ;\n                $slider\n                    ->children('.swiper-wrapper > *')\n                    ->each(function (HtmlPageCrawler $slide) {\n                        if ($slide->hasClass('card')) {\n                            $slide\n                                ->addClass('ui fluid')\n                                ->wrap('<div class=\"swiper-slide\"></div>')\n                            ;\n                        }\n                        elseif ($slide->hasClass('image')) {\n                            $slide\n                                ->removeClass('content')\n                                ->removeClass('rounded')\n                                ->addClass('swiper-slide')\n                            ;\n                        }\n                        else {\n                            $slide->addClass('swiper-slide');\n                        }\n                    })\n                ;\n                // Move prev/next buttons out of container\n                // No longer used, but kept here as reference for how to find parent elements\n                //$slider->parents()->each(function (HtmlPageCrawler $parent) {\n                //    if ($parent->hasClass('nested','slider')) {\n                //        $parent->filter('.swiper-button-prev')->appendTo($parent);\n                //        $parent->filter('.swiper-button-next')->appendTo($parent);\n                //    }\n                //});\n            })\n        ;\n\n        // Fill lightbox with gallery images\n        $lightbox = array();\n        $lightbox =\n            $dom->filter('.gallery.with.lightbox')\n                ->each(function (HtmlPageCrawler $gallery) {\n                    global $modx;\n\n                    // Grab images sources from data attributes\n                    $images =\n                        $gallery\n                            ->filter('.lightbox > img')\n                            ->each(function (HtmlPageCrawler $img) {\n                                global $modx;\n                                return $modx->getChunk('galleryRowImageLightbox', array(\n                                    'src' => $img->attr('data-lightbox-img'),\n                                    'caption' => $img->attr('data-caption'),\n                                    'title' => $img->attr('alt'),\n                                    'classes' => 'swiper-slide',\n                                ));\n                            })\n                    ;\n\n                    // Create lightbox for each gallery\n                    return $modx->getChunk('lightboxOuter', array(\n                        'uid' => $gallery->attr('data-uid'),\n                        'output' => implode($images),\n                    ));\n                })\n        ;\n\n        // Add lightbox to HTML\n        $dom->filter('#footer')\n            ->after(implode($lightbox))\n        ;\n\n        // Manipulate images / SVGs\n        $dom->filter('.ui.image, .ui.svg.image > svg, .ui.svg.image > img')\n            ->each(function (HtmlPageCrawler $img) {\n                $width = $img->getAttribute('width');\n                $height = $img->getAttribute('height');\n\n                // Remove empty width & height\n                if (!$width) $img->removeAttr('width');\n                if (!$height) $img->removeAttr('height');\n            })\n        ;\n\n        // Fix inline form fields\n        $dom->filter('.ui.form .inline.fields > .field')\n            ->removeClass('horizontal')\n            ->removeClass('vertical')\n            ->filter('label')\n            ->each(function(HtmlPageCrawler $label) {\n                if($label->getInnerHtml() === '') {\n                    $label->addClass('hidden');\n                }\n            })\n        ;\n        $dom->filter('.ui.form .inline.fields > .wide.field > .dropdown')\n            ->addClass('fluid')\n        ;\n\n        // Disable steps following an active step\n        $dom->filter('.ui.consecutive.steps .active.step')\n            ->each(function (HtmlPageCrawler $step) {\n                $step\n                    ->nextAll()\n                    ->addClass('disabled')\n                ;\n            })\n        ;\n        // Mark previous steps as completed\n        $dom->filter('.ui.completable.steps .active.step')\n            ->each(function (HtmlPageCrawler $step) {\n                $step\n                    ->previousAll()\n                    ->addClass('completed')\n                ;\n            })\n        ;\n        // Completed steps can't be disabled\n        $dom->filter('.ui.steps .completed.step')->removeClass('disabled');\n\n        // Make sure AjaxUpload scripts are run after jQuery is loaded\n        $dom->filter('script')\n            ->each(function (HtmlPageCrawler $script) {\n                $src = $script->getAttribute('src');\n                $code = $script->getInnerHtml();\n\n                // Defer loading of AjaxUpload JS file\n                if (strpos($src,'ajaxupload') !== false) {\n                    $script->setAttribute('defer','');\n                }\n\n                // Wait for DOMContentLoaded event instead of using document.ready\n                if (strpos($code,'$(document).ready') !== false) {\n                    $code = str_replace('/* <![CDATA[ */', '', $code);\n                    $code = str_replace('/* ]]> */', '', $code);\n\n                    $script->setInnerHtml(\n                        str_replace(\n                            '$(document).ready(function ()',\n                            'window.addEventListener(\\'DOMContentLoaded\\', function()',\n                            $code\n                        )\n                    );\n                }\n            })\n        ;\n\n        // Fix ID conflicts in project hub\n        $dom->filter('#hub .pattern.segment#content')->setAttribute('id','content-global');\n        $dom->filter('#hub .pattern.segment#css')->setAttribute('id','css-global');\n        $dom->filter('#hub .pattern.segment#footer')->setAttribute('id','footer-global');\n        $dom->filter('#hub .pattern.segment#head')->setAttribute('id','head-global');\n        $dom->filter('#hub .pattern.segment#script')->setAttribute('id','script-global');\n\n        // Change links to fixed IDs\n        $dom->filter('#hub .pattern.segment .list a.item')\n            ->each(function (HtmlPageCrawler $link) {\n                $href = $link->getAttribute('href');\n                switch ($href) {\n                    case ($href == 'patterns/organisms#content'):\n                    case ($href == 'patterns/organisms#css'):\n                    case ($href == 'patterns/organisms#footer'):\n                    case ($href == 'patterns/organisms#head'):\n                    case ($href == 'patterns/organisms#script'):\n                        $link->setAttribute('href', $href . '-global');\n                        break;\n                }\n            })\n        ;\n\n        // Save manipulated DOM\n        $output = $dom->saveHTML();\n\n        break;\n}"
properties: 'a:1:{s:13:"elementStatus";a:7:{s:4:"name";s:13:"elementStatus";s:4:"desc";s:37:"romanesco.manipulatedom.elementStatus";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:5:"solid";s:7:"lexicon";s:20:"romanesco:properties";s:4:"area";s:0:"";}}'

-----


/**
 * ManipulateDOM plugin
 *
 * This plugin utilizes HtmlPageDom, a page crawler that can manipulate DOM
 * elements for us. Yes, that is exactly what jQuery does... But now we can do
 * it server side, before the page is rendered. Much faster and more reliable.
 *
 * @var modX $modx
 * @var array $scriptProperties
 * @package romanesco
 */

if (!class_exists(\Wa72\HtmlPageDom\HtmlPageCrawler::class)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[HtmlPageDom] Class not found!');
    return;
}

use \Wa72\HtmlPageDom\HtmlPageCrawler;

switch ($modx->event->name) {
    case 'OnWebPagePrerender':

        // Check if content type is text/html
        if (!in_array($modx->resource->get('content_type'), [1,11])) {
            break;
        }

        // Get processed output of resource
        $output = &$modx->resource->_output;

        // Feed output to HtmlPageDom
        $dom = new HtmlPageCrawler($output);

        // Add non-white class to body if custom background is set
        try {
            if ($modx->getObject('cgSetting', array('key' => 'theme_page_background_color'))->get('value') !== 'ffffff') {
                $dom->filter('body')->addClass('non-white');
            }
        }
        catch (Error $e) {
            $modx->log(modX::LOG_LEVEL_ERROR, $e);
        }

        // Read inverted parameter from URL (for testing purposes)
        $invertLayouts = $_GET['inverted'] ?? 0;
        if ($invertLayouts) {
            $dom->filter('.ui.menu')
                ->addClass('inverted')
            ;
            $dom->filter('.vertical.stripe.segment.white')
                ->removeClass('white')
                ->addClass('inverted')
            ;
            $dom->filter('.vertical.backyard.segment.secondary')
                ->removeClass('secondary')
                ->addClass('inverted black')
            ;
        }

        // Add header class to content headers without class name
        $dom->filter('h1:not(.header)')->addClass('ui header');
        $dom->filter('h2:not(.header)')->addClass('ui header');
        $dom->filter('h3:not(.header)')->addClass('ui header');
        $dom->filter('h4:not(.header)')->addClass('ui header');
        $dom->filter('h5:not(.header)')->addClass('ui header');

        // Inject inverted classes to elements inside inverted segments
        $dom->filter('.inverted.segment')
            ->each(function (HtmlPageCrawler $segment) {

                // Define elements that need to receive the inverted class
                $elements = array(
                    '.header',
                    '.grid',
                    'a:not(.button)',
                    '.button:not(.primary):not(.secondary)',
                    '.subtitle',
                    '.lead',
                    '.list',
                    '.quote',
                    '.divider:not(.hidden)',
                    '.accordion:not(.styled)',
                    '.text.menu',
                    '.secondary.menu',
                    '.basic.form',
                    '.table',
                    '.steps',
                );

                // Revert inverted styling inside these nested elements
                $exceptions = array(
                    '.segment:not(.inverted):not(.transparent)',
                    '.card',
                    '.message',
                    '.accordion:not(.inverted)',
                    '.popup:not(.inverted)',
                    '.tabbed.menu',
                    '.form:not(.basic)',
                    '.leaflet-container',
                );

                // Prevent elements from having the same color as their parent background
                if ($segment->hasClass('primary-color') || $segment->hasClass('secondary-color') || $segment->hasClass('secondary')) {
                    $segment
                        ->filter('.primary.button')
                        ->addClass('white inverted')
                    ;
                    $segment
                        ->filter('.secondary.button')
                        ->removeClass('secondary')
                        ->addClass('inverted')
                    ;
                    $segment
                        ->filter('.bottom.attached.primary.button')
                        ->removeClass('primary')
                        ->addClass('secondary')
                    ;
                }

                // Elements
                foreach ($elements as $element) {
                    $segment
                        ->filter($element)
                        ->addClass('inverted')
                    ;
                }

                // Exceptions
                foreach ($exceptions as $exception) {
                    $segment
                        ->filter($exception)
                        ->each(function(HtmlPageCrawler $node) {
                            $node
                                ->filter('.inverted')
                                ->removeClass('inverted')
                            ;
                            $node
                                ->filter('.ui.white.button')
                                ->removeClass('white')
                            ;
                        })
                    ;
                }
            })
        ;

        // Remove rows from grids that have a reversed column order on mobile
        $dom->filter('.ui.reversed.grid > .row')->unwrapInner();

        // If grids are stackable on tablet, also hide (or show) designated mobile elements
        $dom->filter('.ui[class*="stackable on tablet"].grid [class*="mobile hidden"]')
            ->removeClass('mobile')
            ->removeClass('hidden')
            ->addClass('tablet or lower hidden')
        ;
        $dom->filter('.ui[class*="stackable on tablet"].grid [class*="mobile only"]')
            ->addClass('tablet only')
        ;

        // Responsive image sizes might be incorrect in responsive grids
        $dom->filter('.ui.stackable.grid, .ui.doubling.grid, .ui.stackable.cards')
            ->each(function(HtmlPageCrawler $grid) {
                $targetImg = '.row > .column > .ui.image > img, .column > .ui.image > img';
                if ($grid->matches('.cards')) {
                    $targetImg = '.card > .image > img';
                }

                // Tag images in stackable on tablet and two column doubling grids
                if ($grid->matches('[class*="stackable on tablet"]') || $grid->matches('[class*="two column"].doubling')) {
                    $grid->children($targetImg)->addClass('tablet-expand-full');
                }
                // Do the same for doubling grids with more than two columns
                else if ($grid->matches('.doubling:not([class*="two column"]):not([class*="equal width"])')) {
                    $grid->children($targetImg)->addClass('tablet-expand-half');

                    if ($grid->matches('.doubling:not(.stackable)')) {
                        $grid->children($targetImg)->addClass('mobile-expand-half');
                    }
                }

                // Only target direct descendants
                $grid->children($targetImg)
                    ->each(function(HtmlPageCrawler $img) {
                        $dataSizes = $img->getAttribute('data-sizes');
                        $sizes = $dataSizes ?? $img->getAttribute('sizes');

                        if (!$sizes) return;

                        // If lazy load is enabled, sizes are stored in data-sizes
                        $attribute = 'sizes';
                        if ($dataSizes) $attribute = 'data-sizes';

                        // Set mobile breakpoints to 100vw, because stacked means full width
                        $stackedSizes = preg_replace('/\(min-width: 360px\).+/','(min-width: 360px) 100vw,', $sizes);
                        $stackedSizes = preg_replace('/\(max-width: 359px\).+/','(max-width: 359px) 100vw', $stackedSizes);

                        // Set optional sizes, if indicated
                        if ($img->matches('.tablet-expand-full')) {
                            $stackedSizes = preg_replace('/\(min-width: 768px\).+/','(min-width: 768px) 100vw,', $stackedSizes);
                        }
                        if ($img->matches('.tablet-expand-half')) {
                            $stackedSizes = preg_replace('/\(min-width: 768px\).+/','(min-width: 768px) 50vw,', $stackedSizes);
                        }
                        if ($img->matches('.mobile-expand-half')) {
                            $stackedSizes = preg_replace('/\(min-width: 360px\).+/','(min-width: 360px) 50vw,', $stackedSizes);
                            $stackedSizes = preg_replace('/\(max-width: 359px\).+/','(max-width: 359px) 50vw', $stackedSizes);
                        }

                        $img->setAttribute($attribute, $stackedSizes);
                    })
                ;
            })
        ;

        // Add class to empty grid columns
        $dom->filter('.ui.grid .column')
            ->each(function(HtmlPageCrawler $column) {
                if($column->getInnerHtml() === '') {
                    $column->addClass('empty');
                }
            })
        ;

        // Display bullets above list items in centered lists
        $dom->filter('.ui.center.aligned')
            ->each(function(HtmlPageCrawler $container) {
                $container->filter('.ui.list')->addClass('vertical');
                $container->filter('.aligned:not(.center) .ui.vertical.list')->removeClass('vertical');
            })
        ;

        // Make regular divider headers smaller
        $dom->filter('span.ui.divider.header')
            ->addClass('tiny')
        ;

        // Transform regular tables into SUI tables
        $dom->filter('table:not(.ui.table)')
            ->addClass('ui table')
        ;

        // Apply Swiper classes to appropriate slide elements
        $dom->filter('.swiper')
            ->each(function (HtmlPageCrawler $slider) {
                $slider
                    ->children('.nested.overview')
                    ->removeClass('stackable')
                    ->removeClass('doubling')
                    ->addClass('swiper-wrapper')
                ;
                $slider
                    ->children('.gallery')
                    ->addClass('swiper-wrapper')
                ;
                $slider
                    ->children('.cards')
                    ->addClass('swiper-wrapper')
                ;
                $slider
                    ->children('.swiper-wrapper > *')
                    ->each(function (HtmlPageCrawler $slide) {
                        if ($slide->hasClass('card')) {
                            $slide
                                ->addClass('ui fluid')
                                ->wrap('<div class="swiper-slide"></div>')
                            ;
                        }
                        elseif ($slide->hasClass('image')) {
                            $slide
                                ->removeClass('content')
                                ->removeClass('rounded')
                                ->addClass('swiper-slide')
                            ;
                        }
                        else {
                            $slide->addClass('swiper-slide');
                        }
                    })
                ;
                // Move prev/next buttons out of container
                // No longer used, but kept here as reference for how to find parent elements
                //$slider->parents()->each(function (HtmlPageCrawler $parent) {
                //    if ($parent->hasClass('nested','slider')) {
                //        $parent->filter('.swiper-button-prev')->appendTo($parent);
                //        $parent->filter('.swiper-button-next')->appendTo($parent);
                //    }
                //});
            })
        ;

        // Fill lightbox with gallery images
        $lightbox = array();
        $lightbox =
            $dom->filter('.gallery.with.lightbox')
                ->each(function (HtmlPageCrawler $gallery) {
                    global $modx;

                    // Grab images sources from data attributes
                    $images =
                        $gallery
                            ->filter('.lightbox > img')
                            ->each(function (HtmlPageCrawler $img) {
                                global $modx;
                                return $modx->getChunk('galleryRowImageLightbox', array(
                                    'src' => $img->attr('data-lightbox-img'),
                                    'caption' => $img->attr('data-caption'),
                                    'title' => $img->attr('alt'),
                                    'classes' => 'swiper-slide',
                                ));
                            })
                    ;

                    // Create lightbox for each gallery
                    return $modx->getChunk('lightboxOuter', array(
                        'uid' => $gallery->attr('data-uid'),
                        'output' => implode($images),
                    ));
                })
        ;

        // Add lightbox to HTML
        $dom->filter('#footer')
            ->after(implode($lightbox))
        ;

        // Manipulate images / SVGs
        $dom->filter('.ui.image, .ui.svg.image > svg, .ui.svg.image > img')
            ->each(function (HtmlPageCrawler $img) {
                $width = $img->getAttribute('width');
                $height = $img->getAttribute('height');

                // Remove empty width & height
                if (!$width) $img->removeAttr('width');
                if (!$height) $img->removeAttr('height');
            })
        ;

        // Fix inline form fields
        $dom->filter('.ui.form .inline.fields > .field')
            ->removeClass('horizontal')
            ->removeClass('vertical')
            ->filter('label')
            ->each(function(HtmlPageCrawler $label) {
                if($label->getInnerHtml() === '') {
                    $label->addClass('hidden');
                }
            })
        ;
        $dom->filter('.ui.form .inline.fields > .wide.field > .dropdown')
            ->addClass('fluid')
        ;

        // Disable steps following an active step
        $dom->filter('.ui.consecutive.steps .active.step')
            ->each(function (HtmlPageCrawler $step) {
                $step
                    ->nextAll()
                    ->addClass('disabled')
                ;
            })
        ;
        // Mark previous steps as completed
        $dom->filter('.ui.completable.steps .active.step')
            ->each(function (HtmlPageCrawler $step) {
                $step
                    ->previousAll()
                    ->addClass('completed')
                ;
            })
        ;
        // Completed steps can't be disabled
        $dom->filter('.ui.steps .completed.step')->removeClass('disabled');

        // Make sure AjaxUpload scripts are run after jQuery is loaded
        $dom->filter('script')
            ->each(function (HtmlPageCrawler $script) {
                $src = $script->getAttribute('src');
                $code = $script->getInnerHtml();

                // Defer loading of AjaxUpload JS file
                if (strpos($src,'ajaxupload') !== false) {
                    $script->setAttribute('defer','');
                }

                // Wait for DOMContentLoaded event instead of using document.ready
                if (strpos($code,'$(document).ready') !== false) {
                    $code = str_replace('/* <![CDATA[ */', '', $code);
                    $code = str_replace('/* ]]> */', '', $code);

                    $script->setInnerHtml(
                        str_replace(
                            '$(document).ready(function ()',
                            'window.addEventListener(\'DOMContentLoaded\', function()',
                            $code
                        )
                    );
                }
            })
        ;

        // Fix ID conflicts in project hub
        $dom->filter('#hub .pattern.segment#content')->setAttribute('id','content-global');
        $dom->filter('#hub .pattern.segment#css')->setAttribute('id','css-global');
        $dom->filter('#hub .pattern.segment#footer')->setAttribute('id','footer-global');
        $dom->filter('#hub .pattern.segment#head')->setAttribute('id','head-global');
        $dom->filter('#hub .pattern.segment#script')->setAttribute('id','script-global');

        // Change links to fixed IDs
        $dom->filter('#hub .pattern.segment .list a.item')
            ->each(function (HtmlPageCrawler $link) {
                $href = $link->getAttribute('href');
                switch ($href) {
                    case ($href == 'patterns/organisms#content'):
                    case ($href == 'patterns/organisms#css'):
                    case ($href == 'patterns/organisms#footer'):
                    case ($href == 'patterns/organisms#head'):
                    case ($href == 'patterns/organisms#script'):
                        $link->setAttribute('href', $href . '-global');
                        break;
                }
            })
        ;

        // Save manipulated DOM
        $output = $dom->saveHTML();

        break;
}