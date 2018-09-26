<?php
/**
 * @author Afanasyev Pavel <p.afanasev@graphgrail.com>
 */

return [
    'iPavlov' => read_json_config(__DIR__ . '/aimodels/iPavlov.json'),
];

function read_json_config(string $file_name)
{
    $config = \json_decode(\file_get_contents($file_name), true);
    return $config;
}