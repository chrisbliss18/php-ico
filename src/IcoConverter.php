<?php

namespace Ossobuffo\PhpIco;

/*
Copyright 2011-2013 Chris Jean & iThemes
Licensed under GPLv2 or above
*/

class IcoConverter
{
    /**
     * Images in the PNG format.
     *
     * @var array
     */
    private $images = [];

    /**
     * Size of BMP image header.
     */
    const IMAGE_HEADER_SIZE = 40;

    /**
     * Size of icon directory entry in the BMP index.
     */
    const ICON_DIR_ENTRY_SIZE = 16;

    /**
     * Constructor - Create a new ICO generator.
     *
     * If the constructor is not passed a file, a file will need to be supplied
     * using the addImage() function in order to generate an ICO file.
     *
     * @param string|bool $file
     *      Optional. Path to the source image file.
     * @param array $sizes
     *      Optional. An array of sizes (each size is an array with a width and
     *      height) that the source image should be rendered at in the
     *      generated ICO file. If sizes are not supplied, the size of the
     *      source image will be used.
     */
    public function __construct($file = false, array $sizes = [])
    {
        if (false != $file) {
            $this->addImage($file, $sizes);
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
    public function addImage($file, $sizes = [])
    {
        if (false === ($image = $this->loadImageFile($file))) {
            return false;
        }

        if (empty($sizes)) {
            $sizes = [imagesx($image), imagesy($image)];
        }

        // If just a single size was passed, put it in array.
        if (!is_array($sizes[0])) {
            $sizes = [$sizes];
        }

        foreach ((array)$sizes as $size) {
            list($width, $height) = $size;

            $newImage = imagecreatetruecolor($width, $height);

            imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);

            $sourceWidth = imagesx($image);
            $sourceHeight = imagesy($image);

            $success = imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

            if (false === $success) {
                continue;
            }

            $this->addImageData($newImage);
        }

        return true;
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
    public function saveIco($file)
    {
        if (false === ($data = $this->getIcoData())) {
            return false;
        }

        if (false === ($fh = fopen($file, 'w'))) {
            return false;
        }

        if (false === (fwrite($fh, $data))) {
            fclose($fh);
            return false;
        }

        fclose($fh);
        return true;
    }

    /**
     * Generate final ICO data by creating a file header & adding image data.
     */
    private function getIcoData()
    {
        if (!is_array($this->images) || empty($this->images)) {
            return false;
        }

        $data = pack('vvv', 0, 1, count($this->images));
        $pixelData = '';

        $offset = 6 + (self::ICON_DIR_ENTRY_SIZE * count($this->images));

        foreach ($this->images as $image) {
            $data .= pack(
                'CCCCvvVV',
                $image['width'],
                $image['height'],
                $image['color_palette_colors'],
                0,
                1,
                $image['bits_per_pixel'],
                $image['size'],
                $offset
            );
            $pixelData .= $image['data'];

            $offset += $image['size'];
        }

        $data .= $pixelData;
        unset($pixelData);
        return $data;
    }

    /**
     * Take a GD image resource and change it into a raw BMP format.
     *
     * The BMP data is appended to the $this->images array.
     *
     * @param resource $image
     *      The GD image to be changed into BMP.
     */
    private function addImageData($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $pixelData = [];

        $opacityData = [];
        $currentOpacityVal = 0;

        for ($y = $height - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);

                $alpha = ($color & 0x7F000000) >> 24;
                $alpha = (1 - ($alpha / 127)) * 255;

                $color &= 0xFFFFFF;
                $color |= 0xFF000000 & ($alpha << 24);

                $pixelData[] = $color;


                $opacity = ($alpha <= 127) ? 1 : 0;

                $currentOpacityVal = ($currentOpacityVal << 1) | $opacity;

                if ((($x + 1) % 32) == 0) {
                    $opacityData[] = $currentOpacityVal;
                    $currentOpacityVal = 0;
                }
            }

            if (($x % 32) > 0) {
                while (($x++ % 32) > 0) {
                    $currentOpacityVal = $currentOpacityVal << 1;
                }

                $opacityData[] = $currentOpacityVal;
                $currentOpacityVal = 0;
            }
        }

        $colorMaskSize = $width * $height * 4;
        $opacityMaskSize = (ceil($width / 32) * 4) * $height;

        $data = pack('VVVvvVVVVVV', 40, $width, ($height * 2), 1, 32, 0, 0, 0, 0, 0, 0);

        foreach ($pixelData as $color) {
            $data .= pack('V', $color);
        }

        foreach ($opacityData as $opacity) {
            $data .= pack('N', $opacity);
        }

        $image = [
            'width' => $width,
            'height' => $height,
            'color_palette_colors' => 0,
            'bits_per_pixel' => 32,
            'size' => self::IMAGE_HEADER_SIZE + $colorMaskSize + $opacityMaskSize,
            'data' => $data,
        ];

        $this->images[] = $image;
    }

    /**
     * Read in the source image file and convert it into a GD image resource.
     *
     * @param string $file
     *      Name of an image file to be loaded.
     *
     * @return resource
     *      The GD image resource created from the image file.
     */
    private function loadImageFile($file)
    {
        // Run a cheap check to verify that it is an image file.
        if (false === ($size = getimagesize($file))) {
            return false;
        }

        if (false === ($fileData = file_get_contents($file))) {
            return false;
        }

        if (false === ($im = imagecreatefromstring($fileData))) {
            return false;
        }

        unset($fileData);
        return $im;
    }
}
