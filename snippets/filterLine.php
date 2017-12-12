id: 112
name: filterLine
description: 'Search the input for lines containing a specific string. And then return those lines.'
category: f_modifiers
snippet: "$lines = $modx->getOption('input', $scriptProperties, $input);\n$file = $modx->getOption('file', $scriptProperties, '');\n$search = $modx->getOption('searchString', $scriptProperties, $options);\n$limit = $modx->getOption('limit', $scriptProperties, 10);\n$tpl = $modx->getOption('tpl', $scriptProperties, '');\n\n// Check first if we're dealing with an external file\nif ($file) {\n    $lines = file_get_contents($file);\n}\n\n// Create an array of all lines inside the input\n$lines = explode(\"\\n\", $lines);\n$i = 0;\n\n// Check if the line contains the string we're looking for, and print if it does\nforeach ($lines as $line) {\n    if(strpos($line, $search) !== false) {\n        $output[] = $line;\n\n        $i++;\n        if($i >= $limit) {\n            break;\n        }\n\n        if ($tpl) {\n            $output[] = $modx->getChunk($tpl, array(\n                'content' => $line,\n            ));\n        }\n    }\n}\n\nif (is_array($output)) {\n    return implode('<br>', $output);\n} else {\n    return $output;\n}"
properties: 'a:0:{}'
content: "$lines = $modx->getOption('input', $scriptProperties, $input);\n$file = $modx->getOption('file', $scriptProperties, '');\n$search = $modx->getOption('searchString', $scriptProperties, $options);\n$limit = $modx->getOption('limit', $scriptProperties, 10);\n$tpl = $modx->getOption('tpl', $scriptProperties, '');\n\n// Check first if we're dealing with an external file\nif ($file) {\n    $lines = file_get_contents($file);\n}\n\n// Create an array of all lines inside the input\n$lines = explode(\"\\n\", $lines);\n$i = 0;\n\n// Check if the line contains the string we're looking for, and print if it does\nforeach ($lines as $line) {\n    if(strpos($line, $search) !== false) {\n        $output[] = $line;\n\n        $i++;\n        if($i >= $limit) {\n            break;\n        }\n\n        if ($tpl) {\n            $output[] = $modx->getChunk($tpl, array(\n                'content' => $line,\n            ));\n        }\n    }\n}\n\nif (is_array($output)) {\n    return implode('<br>', $output);\n} else {\n    return $output;\n}"

-----


$lines = $modx->getOption('input', $scriptProperties, $input);
$file = $modx->getOption('file', $scriptProperties, '');
$search = $modx->getOption('searchString', $scriptProperties, $options);
$limit = $modx->getOption('limit', $scriptProperties, 10);
$tpl = $modx->getOption('tpl', $scriptProperties, '');

// Check first if we're dealing with an external file
if ($file) {
    $lines = file_get_contents($file);
}

// Create an array of all lines inside the input
$lines = explode("\n", $lines);
$i = 0;

// Check if the line contains the string we're looking for, and print if it does
foreach ($lines as $line) {
    if(strpos($line, $search) !== false) {
        $output[] = $line;

        $i++;
        if($i >= $limit) {
            break;
        }

        if ($tpl) {
            $output[] = $modx->getChunk($tpl, array(
                'content' => $line,
            ));
        }
    }
}

if (is_array($output)) {
    return implode('<br>', $output);
} else {
    return $output;
}