<?php

use Chrisbliss18\PhpIco;

require_once __DIR__ . '/src/PhpIco.php';

/*
Copyright 2011-2013 Chris Jean & iThemes
Licensed under GPLv2 or above
*/

/**
 * Class PHP_ICO
 * This class provides a thin wrapper around Chrisbliss18/PhpIco for backwards
 * compatibility.
 */
class PHP_ICO
{
    /**
     * Reference to the class we are wrapping.
     *
     * @var PhpIco
     */
    private $phpIco;

    /**
     * Constructor - Create a new ICO generator.
     *
     * If the constructor is not passed a file, a file will need to be supplied
     * using the {@link PHP_ICO::add_image} function in order to generate an
     * ICO file.
     *
     * @param string|bool $file
     *      Optional. Path to the source image file.
     * @param array $sizes
     *      Optional. An array of sizes (each size is an array with a width and
     *      height) that the source image should be rendered at in the
     *      generated ICO file. If sizes are not supplied, the size of the
     *      source image will be used.
     */
    public function __construct($file = false, array $sizes = array())
    {
        try {
            $this->phpIco = new PhpIco($file, $sizes);
        } catch (\RuntimeException $e) {
            trigger_error($e->getMessage());
        }
    }

    /**
     * Add an image to the generator.
     *
     * This function adds a source image to the generator. It serves two main
     * purposes: add a source image if one was not supplied to the constructor
     * and to add additional source images so that different images can be
     * supplied for different sized images in the resulting ICO file. For
     * instance, a small source image can be used for the small resolutions
     * while a larger source image can be used for large resolutions.
     *
     * @param string $file
     *      Path to the source image file.
     * @param array $sizes
     *      Optional. An array of sizes (each size is an array with a width
     *      and height) that the source image should be rendered at in the
     *      generated ICO file. If sizes are not supplied, the size of the
     *      source image will be used.
     *
     * @return boolean
     *      true on success and false on failure.
     */
    public function add_image($file, $sizes = array())
    {
        return $this->phpIco->addImage($file, $sizes);
    }

    /**
     * Write the ICO file data to a file path.
     *
     * @param string $file
     *      Path to save the ICO file data into.
     *
     * @return boolean
     *      true on success and false on failure.
     */
    public function save_ico($file)
    {
        return $this->phpIco->saveIco($file);
    }
}
