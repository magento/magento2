<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Varien
 * @package     Varien_Image
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Varien_Image_Adapter_InterfaceTest extends PHPUnit_Framework_TestCase
{
    /**
     * Adapter classes for test
     *
     * @var array
     */
    protected $_adapters = array(
        'Varien_Image_Adapter_Gd2',
        'Varien_Image_Adapter_ImageMagick'
    );

    /**
     * Add adapters to each data provider case
     *
     * @param array $data
     * @return array
     */
    protected function _prepareData($data)
    {
        $result   = array();
        foreach ($this->_adapters as $adapter) {
            foreach ($data as $row) {
                $row[] = new $adapter;
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * Returns fixture image size
     *
     * @return array
     */
    protected function _getFixtureImageSize()
    {
        return array(311, 162);
    }

    /**
     * Compare two colors with some epsilon
     *
     * @param array $colorBefore
     * @param array $colorAfter
     * @return bool
     */
    protected function _compareColors($colorBefore, $colorAfter)
    {
        // get different epsilon for 8 bit (max value = 255) & 16 bit (max value = 65535) images (eps = 5%)
        $eps = max($colorAfter) > 255 ? 3500 : 20;

        $result = true;
        foreach ($colorAfter as $i => $v) {
            if (abs($colorBefore[$i] - $v) > $eps) {
                $result = false;
                break;
            }
        }
        return $result;
    }

    /**
     * Randomly returns fixtures image path by pattern
     *
     * @param string $pattern
     * @return string|null
     */
    protected function _getFixture($pattern)
    {
        $dir  = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files'. DIRECTORY_SEPARATOR;
        $data = glob($dir . $pattern);

        if (!empty($data)) {
            $index = isset($data[1]) ? array_rand($data) : 0;
            return $data[$index];
        }

        return null;
    }

    /**
     * Check is format supported.
     *
     * @param string $image
     * @param Varien_Image_Adapter_Abstract $adapter
     * @return bool
     */
    protected function _isFormatSupported($image, $adapter)
    {
        $data = pathinfo($image);
        $supportedTypes = $adapter->getSupportedFormats();
        return $image && file_exists($image)
            && in_array(strtolower($data['extension']), $supportedTypes);
    }

    /**
     * Checks is adapter testable.
     * Mark test as skipped if not
     *
     * @param Varien_Image_Adapter_Abstract $adapter
     */
    protected function _isAdapterAvailable($adapter)
    {
        try {
            $adapter->checkDependencies();
        } catch (Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    /**
     * Checks if all dependencies are loaded
     * @param Varien_Image_Adapter_Abstract $adapter
     *
     * @dataProvider adaptersDataProvider
     */
    public function testCheckDependencies($adapter)
    {
        $this->_isAdapterAvailable($adapter);
    }

    public function adaptersDataProvider()
    {
        $data = array();
        foreach ($this->_adapters as $adapter) {
            $data[] = array(new $adapter);
        }
        return $data;
    }

    /**
     * @param string $image
     * @param Varien_Image_Adapter_Abstract $adapter
     *
     * @depends testCheckDependencies
     * @dataProvider openDataProvider
     */
    public function testOpen($image, $adapter)
    {
        $this->_isAdapterAvailable($adapter);
        try  {
            $adapter->open($image);
        } catch (Exception $e) {
            $result = $this->_isFormatSupported($image, $adapter);
            $this->assertFalse($result);
        }
    }

    public function openDataProvider()
    {
        return $this->_prepareData(array(
            array(null),
            array($this->_getFixture('image_adapters_test.png')),
            array($this->_getFixture('image_adapters_test.tiff')),
            array($this->_getFixture('image_adapters_test.bmp'))
        ));
    }

    /**
     * @param string $image
     * @param Varien_Image_Adapter_Abstract $adapter
     *
     * @dataProvider openDataProvider
     * @depends testOpen
     */
    public function testImageSize($image, $adapter)
    {
        $this->_isAdapterAvailable($adapter);
        try {
            $adapter->open($image);
            $this->assertEquals($this->_getFixtureImageSize(), array(
                $adapter->getOriginalWidth(),
                $adapter->getOriginalHeight()
            ));
        } catch (Exception $e) {
            $result = $this->_isFormatSupported($image, $adapter);
            $this->assertFalse($result);
        }
    }

    /**
     * @param string $image
     * @param array $tempPath (dirName, newName)
     * @param Varien_Image_Adapter_Abstract $adapter
     *
     * @dataProvider saveDataProvider
     * @depends testOpen
     */
    public function testSave($image, $tempPath, $adapter)
    {
        $this->_isAdapterAvailable($adapter);
        $adapter->open($image);
        try {
            call_user_func_array(array($adapter, 'save'), $tempPath);
            $tempPath = join('', $tempPath);
            $this->assertFileExists($tempPath);
            unlink($tempPath);
        } catch (Exception $e) {
            $this->assertFalse(is_dir($tempPath[0]) && is_writable($tempPath[0]));
        }
    }

    public function saveDataProvider()
    {
        $dir = Magento_Test_Bootstrap::getInstance()->getTmpDir() . DIRECTORY_SEPARATOR;
        return $this->_prepareData(array(
            array(
                $this->_getFixture('image_adapters_test.png'),
                array($dir . uniqid('test_image_adapter'))
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                array($dir, uniqid('test_image_adapter'))
            )
        ));
    }

    /**
     * @param string $image
     * @param array $dims (width, height)
     * @param Varien_Image_Adapter_Abstract $adapter
     *
     * @dataProvider resizeDataProvider
     * @depends testOpen
     */
    public function testResize($image, $dims, $adapter)
    {
        $this->_isAdapterAvailable($adapter);
        $adapter->open($image);
        try {
            $adapter->resize($dims[0], $dims[1]);
            $this->assertEquals($dims, array(
                $adapter->getOriginalWidth(),
                $adapter->getOriginalHeight()
            ));
        } catch (Exception $e) {
            $result = $dims[0] !== null && $dims[0] <= 0
                || $dims[1] !== null && $dims[1] <= 0
                || empty($$dims[0]) && empty($$dims[1]);
            $this->assertTrue($result);
        }
    }

    public function resizeDataProvider()
    {
        return $this->_prepareData(array(
            array(
                $this->_getFixture('image_adapters_test.png'),
                array(150, 70)
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                array(null, 70)
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                array(100, null)
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                array(null, null)
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                array(-100, -50)
            )
        ));
    }

    /**
     * @param string $image
     * @param int $angle
     * @param array $pixel
     * @param Varien_Image_Adapter_Abstract $adapter
     *
     * @dataProvider rotateDataProvider
     * @depends testOpen
     */
    public function testRotate($image, $angle, $pixel, $adapter)
    {
        $this->_isAdapterAvailable($adapter);
        $adapter->open($image);

        $size = array(
            $adapter->getOriginalWidth(),
            $adapter->getOriginalHeight()
        );

        $colorBefore = $adapter->getColorAt($pixel['x'], $pixel['y']);
        $adapter->rotate($angle);

        $newPixel = $this->_convertCoordinates($pixel, $angle, $size, array(
            $adapter->getOriginalWidth(),
            $adapter->getOriginalHeight()
        ));
        $colorAfter  = $adapter->getColorAt($newPixel['x'], $newPixel['y']);

        $result = $this->_compareColors($colorBefore, $colorAfter);
        $this->assertTrue($result, join(',', $colorBefore) . ' not equals ' . join(',', $colorAfter));
    }

    /**
     * Get pixel coordinates after rotation
     *
     * @param array $pixel ('x' => ..., 'y' => ...)
     * @param int $angle
     * @param array $oldSize (width, height)
     * @param array $size (width, height)
     * @return array
     */
    protected function _convertCoordinates($pixel, $angle, $oldSize, $size)
    {
        $angle  = $angle * pi() / 180;
        $center = array(
            'x' => $oldSize[0] / 2,
            'y' => $oldSize[1] / 2,
        );

        $pixel['x'] -= $center['x'];
        $pixel['y'] -= $center['y'];
        return array(
            'x' => round($size[0]/2 + $pixel['x'] * cos($angle) + $pixel['y'] * sin($angle), 0),
            'y' => round($size[1]/2 + $pixel['y'] * cos($angle) - $pixel['x'] * sin($angle), 0)
        );
    }

    public function rotateDataProvider()
    {
        return $this->_prepareData(array(
            array(
                $this->_getFixture('image_adapters_test.png'),
                45,
                array('x' => 157, 'y' => 35)
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                48,
                array('x' => 157, 'y' => 35)
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                90,
                array('x' => 250, 'y' => 74)
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                180,
                array('x' => 250, 'y' => 74)
            )
        ));
    }

    /**
     * Checks if watermark exists on the right position
     *
     * @param string $image
     * @param string $watermark
     * @param int $width
     * @param int $height
     * @param float $opacity
     * @param string $position
     * @param int $colorX
     * @param int $colorY
     * @param Varien_Image_Adapter_Abstract $adapter
     *
     * @dataProvider imageWatermarkDataProvider
     * @depends testOpen
     */
    public function testWatermark($image, $watermark, $width, $height, $opacity, $position, $colorX, $colorY, $adapter)
    {
        $this->_isAdapterAvailable($adapter);
        $adapter->open($image);
        $pixel = $this->_prepareColor(array('x' => $colorX, 'y' => $colorY), $position, $adapter);

        $colorBefore = $adapter->getColorAt($pixel['x'], $pixel['y']);
        $adapter->setWatermarkWidth($width)
            ->setWatermarkHeight($height)
            ->setWatermarkImageOpacity($opacity)
            ->setWatermarkPosition($position)
            ->watermark($watermark);
        $colorAfter  = $adapter->getColorAt($pixel['x'], $pixel['y']);

        $result  = $this->_compareColors($colorBefore, $colorAfter);
        $message = join(',', $colorBefore) . ' not equals ' . join(',', $colorAfter);
        $this->assertFalse($result, $message);
    }

    public function imageWatermarkDataProvider()
    {
        return $this->_prepareData(array(
            array(
                $this->_getFixture('image_adapters_test.png'),
                $this->_getFixture('watermark.*'),
                50,
                50,
                100,
                Varien_Image_Adapter_Abstract::POSITION_BOTTOM_RIGHT,
                10,
                10
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                $this->_getFixture('watermark.*'),
                100,
                70,
                100,
                Varien_Image_Adapter_Abstract::POSITION_TOP_LEFT,
                10,
                10
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                $this->_getFixture('watermark.*'),
                100,
                70,
                100,
                Varien_Image_Adapter_Abstract::POSITION_TILE,
                10,
                10
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                $this->_getFixture('watermark.*'),
                100,
                100,
                100,
                Varien_Image_Adapter_Abstract::POSITION_STRETCH,
                10,
                10
            )
        ));
    }

    /**
     * Randomly set colorX and colorY coordinates according image width and height
     *
     * @param array $pixel ('x' => ..., 'y' => ...)
     * @param string $position
     * @param Varien_Image_Adapter_Abstract $adapter
     * @return array
     */
    protected function _prepareColor($pixel, $position, $adapter)
    {
        switch ($position) {
            case Varien_Image_Adapter_Abstract::POSITION_BOTTOM_RIGHT:
                $pixel['x'] = $adapter->getOriginalWidth()  - mt_rand(1, 49);
                $pixel['y'] = $adapter->getOriginalHeight() - mt_rand(1, 49);
                break;
            case Varien_Image_Adapter_Abstract::POSITION_BOTTOM_LEFT:
                $pixel['x'] = mt_rand(1, 49);
                $pixel['y'] = $adapter->getOriginalHeight() - mt_rand(1, 49);
                break;
            case Varien_Image_Adapter_Abstract::POSITION_TOP_LEFT:
                $pixel['x'] = mt_rand(1, 49);
                $pixel['y'] = mt_rand(1, 49);
                break;
            case Varien_Image_Adapter_Abstract::POSITION_TOP_RIGHT:
                $pixel['x'] = $adapter->getOriginalWidth() - mt_rand(0, 49);
                $pixel['y'] = mt_rand(1, 49);
                break;
            case Varien_Image_Adapter_Abstract::POSITION_STRETCH:
            case Varien_Image_Adapter_Abstract::POSITION_TILE:
                $pixel['x'] = mt_rand(1, $adapter->getOriginalWidth() - 1);
                $pixel['y'] = mt_rand(1, $adapter->getOriginalHeight() - 1);
                break;
        }
        return $pixel;
    }

    /**
     * @param string $image
     * @param int $left
     * @param int $top
     * @param int $right
     * @param int $bottom
     * @param Varien_Image_Adapter_Abstract $adapter
     *
     * @dataProvider cropDataProvider
     * @depends testOpen
     */
    public function testCrop($image, $left, $top, $right, $bottom, $adapter)
    {
        $this->_isAdapterAvailable($adapter);
        $adapter->open($image);

        $expectedSize = array(
            $adapter->getOriginalWidth()  - $left - $right,
            $adapter->getOriginalHeight() - $top  - $bottom
        );

        $adapter->crop($top, $left, $right, $bottom);

        $newSize = array(
            $adapter->getOriginalWidth(),
            $adapter->getOriginalHeight()
        );

        $this->assertEquals($expectedSize, $newSize);
    }

    public function cropDataProvider()
    {
        return $this->_prepareData(array(
            array(
                $this->_getFixture('image_adapters_test.png'),
                50, 50, 75, 75
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                20, 50, 35, 35
            ),
            array(
                $this->_getFixture('image_adapters_test.png'),
                0, 0, 0, 0
            )
        ));
    }
}