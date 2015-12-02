<?php
use Ossobuffo\PhpIco\IcoConverter;

require_once __DIR__ . '/../src/IcoConverter.php';

$source = __DIR__ . '/input.png';
$source_small = __DIR__ . '/input-32x32.gif';

// Create an enormous 250x250 .ico file
$destination = __DIR__ . '/output1.ico';
$converter = new IcoConverter($source);
$converter->saveIco($destination);

// Create an .ico file with two sized images from the same source.
$destination = __DIR__ . '/output2.ico';
$dimensions = [[16, 16], [32, 32]];
$converter = new IcoConverter($source, $dimensions);
$converter->saveIco($destination);

// Create an .ico file with two sized images from different sources.
$destination = __DIR__ . '/output3.ico';
$converter = new IcoConverter();
$converter->addImage($source_small, [[32, 32]]);
$converter->addImage($source, [[64, 64]]);
$converter->saveIco($destination);
