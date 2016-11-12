<?php
declare (strict_types=1);

namespace PHP_ICO\Tests;

use PHP_ICO;
use PHPUnit_Framework_TestCase;

/**
 * Class EasyDBTest
 * @package ParagonIE\EasyDB\Tests
 */
class OutputIcoTest extends PHPUnit_Framework_TestCase
{

    public function goodAddImageSingleProvider()
    {
        return array(
            array(
                __DIR__ . DIRECTORY_SEPARATOR . 'test-ico-1.gif',
                array(),
            ),
            array(
                __DIR__ . DIRECTORY_SEPARATOR . 'test-ico-1.jpg',
                array(),
            ),
            array(
                __DIR__ . DIRECTORY_SEPARATOR . 'test-ico-1.png',
                array(),
            ),
            array(
                __DIR__ . DIRECTORY_SEPARATOR . 'test-ico-1.gif',
                array(16,16),
            ),
            array(
                __DIR__ . DIRECTORY_SEPARATOR . 'test-ico-1.jpg',
                array(16, 16),
            ),
            array(
                __DIR__ . DIRECTORY_SEPARATOR . 'test-ico-1.png',
                array(16, 16),
            ),
            array(
                __DIR__ . DIRECTORY_SEPARATOR . 'test-ico-1.gif',
                array(
                    array(16,16),
                ),
            ),
            array(
                __DIR__ . DIRECTORY_SEPARATOR . 'test-ico-1.jpg',
                array(
                    array(16,16),
                ),
            ),
            array(
                __DIR__ . DIRECTORY_SEPARATOR . 'test-ico-1.png',
                array(
                    array(16,16),
                ),
            ),
        );
    }

    public function badAddImageSingleProvider_invalidFile()
    {
        return array(
            array(
                null,
                array(),
            ),
            array(
                false,
                array(),
            ),
            array(
                true,
                array(),
            ),
            array(
                1,
                array(),
            ),
            array(
                '',
                array(),
            ),
            array(
                __DIR__ . DIRECTORY_SEPARATOR . 'test-ico-1.xcf',
                array(),
            ),
        );
    }

    public function badSaveIcoProvider_GetIcoDataReturnsFalse()
    {
        return array(
            array(
                array(),
            ),
        );
    }

    /**
    * @dataProvider goodAddImageSingleProvider
    */
    public function testConstructorOnly($file, $sizes) {
        $this->assertTrue(is_file($file));
        $this->assertTrue(is_readable($file));
        $this->assertTrue(is_file($file . '.ico'));
        $this->assertTrue(is_readable($file . '.ico'));

        $ico = new PHP_ICO($file, $sizes);
        $outputToHere = tempnam(sys_get_temp_dir(), 'PHP_ICO_tests');
        $this->assertTrue($ico->save_ico($outputToHere));
        $this->assertSame(sha1_file($file . '.ico'), sha1_file($outputToHere));
        unlink($outputToHere);
    }

    /**
    * @dataProvider badAddImageSingleProvider_invalidFile
    */
    public function testAddImageBadFiles($file, $sizes) {
        $ico = new PHP_ICO();
        $this->assertFalse($ico->add_image($file, $sizes));
    }

    /**
    * @dataProvider badSaveIcoProvider_GetIcoDataReturnsFalse
    */
    public function testSaveIcoBadData($arrayOfFilesAndSizes)
    {
        $ico = new PHP_ICO();
        foreach ($arrayOfFilesAndSizes as $file => $sizes)
        {
            $ico->add_image($file, $sizes);
        }
        $outputToHere = tempnam(sys_get_temp_dir(), 'PHP_ICO_tests');
        $this->assertFalse($ico->save_ico($outputToHere));
        unlink($outputToHere);
    }
}
