<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @magentoAppIsolation enabled
 */
class InterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Adapter classes for test
     *
     * @var array
     */
    protected $_adapters = [
        \Magento\Framework\Image\Adapter\AdapterInterface::ADAPTER_GD2,
        \Magento\Framework\Image\Adapter\AdapterInterface::ADAPTER_IM,
    ];

    /**
     * Add adapters to each data provider case
     *
     * @param array $data
     * @return array
     */
    protected function _prepareData($data)
    {
        $result = [];
        foreach ($this->_adapters as $adapterType) {
            foreach ($data as $row) {
                $row[] = $adapterType;
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
        return [311, 162];
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
     * Returns fixtures image path by pattern
     *
     * @param string $pattern
     * @return string|null
     */
    protected function _getFixture($pattern)
    {
        $dir = dirname(__DIR__) . '/_files/';
        $data = glob($dir . $pattern);

        if (!empty($data)) {
            return $data[0];
        }

        return null;
    }

    /**
     * Check is format supported.
     *
     * @param string $image
     * @param \Magento\Framework\Image\Adapter\AbstractAdapter $adapter
     * @return bool
     */
    protected function _isFormatSupported($image, $adapter)
    {
        $data = pathinfo($image);
        $supportedTypes = $adapter->getSupportedFormats();
        return $image && file_exists($image) && in_array(strtolower($data['extension']), $supportedTypes);
    }

    /**
     * Checks is adapter testable.
     * Mark test as skipped if not
     *
     * @param string $adapterType
     * @return \Magento\Framework\Image\Adapter\AdapterInterface
     */
    protected function _getAdapter($adapterType)
    {
        try {
            $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
            $adapter = $objectManager->get('Magento\Framework\Image\AdapterFactory')->create($adapterType);
            return $adapter;
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    /**
     * Checks if all dependencies are loaded
     * @param string $adapterType
     *
     * @dataProvider adaptersDataProvider
     */
    public function testCheckDependencies($adapterType)
    {
        $this->_getAdapter($adapterType);
    }

    public function adaptersDataProvider()
    {
        return [
            [\Magento\Framework\Image\Adapter\AdapterInterface::ADAPTER_GD2],
            [\Magento\Framework\Image\Adapter\AdapterInterface::ADAPTER_IM]
        ];
    }

    /**
     * @param string $image
     * @param string $adapterType
     *
     * @depends testCheckDependencies
     * @dataProvider openDataProvider
     */
    public function testOpen($image, $adapterType)
    {
        $adapter = $this->_getAdapter($adapterType);
        try {
            $adapter->open($image);
        } catch (\Exception $e) {
            $result = $this->_isFormatSupported($image, $adapter);
            $this->assertFalse($result);
        }
    }

    public function openDataProvider()
    {
        return $this->_prepareData(
            [
                [null],
                [$this->_getFixture('image_adapters_test.png')],
                [$this->_getFixture('image_adapters_test.tiff')],
                [$this->_getFixture('image_adapters_test.bmp')],
            ]
        );
    }

    /**
     * @param string $adapterType
     * @dataProvider adaptersDataProvider
     */
    public function testGetImage($adapterType)
    {
        $adapter = $this->_getAdapter($adapterType);
        $adapter->open($this->_getFixture('image_adapters_test.png'));
        $this->assertNotEmpty($adapter->getImage());
    }

    /**
     * @param string $image
     * @param string $adapterType
     *
     * @dataProvider openDataProvider
     * @depends testOpen
     */
    public function testImageSize($image, $adapterType)
    {
        $adapter = $this->_getAdapter($adapterType);
        try {
            $adapter->open($image);
            $this->assertEquals(
                $this->_getFixtureImageSize(),
                [$adapter->getOriginalWidth(), $adapter->getOriginalHeight()]
            );
        } catch (\Exception $e) {
            $result = $this->_isFormatSupported($image, $adapter);
            $this->assertFalse($result);
        }
    }

    /**
     * @param string $image
     * @param array $tempPath (dirName, newName)
     * @param string $adapterType
     *
     * @dataProvider saveDataProvider
     * @depends testOpen
     */
    public function testSave($image, $tempPath, $adapterType)
    {
        $adapter = $this->_getAdapter($adapterType);
        $adapter->open($image);
        try {
            call_user_func_array([$adapter, 'save'], $tempPath);
            $tempPath = join('', $tempPath);
            $this->assertFileExists($tempPath);
            unlink($tempPath);
        } catch (\Exception $e) {
            $this->assertFalse(is_dir($tempPath[0]) && is_writable($tempPath[0]));
        }
    }

    public function saveDataProvider()
    {
        $dir = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppTempDir() . '/';
        return $this->_prepareData(
            [
                [$this->_getFixture('image_adapters_test.png'), [$dir . uniqid('test_image_adapter')]],
                [$this->_getFixture('image_adapters_test.png'), [$dir, uniqid('test_image_adapter')]],
            ]
        );
    }

    /**
     * @param string $image
     * @param array $dims (width, height)
     * @param string $adapterType
     *
     * @dataProvider resizeDataProvider
     * @depends testOpen
     */
    public function testResize($image, $dims, $adapterType)
    {
        $adapter = $this->_getAdapter($adapterType);
        $adapter->open($image);
        try {
            $adapter->resize($dims[0], $dims[1]);
            $this->assertEquals($dims, [$adapter->getOriginalWidth(), $adapter->getOriginalHeight()]);
        } catch (\Exception $e) {
            $result = $dims[0] !== null && $dims[0] <= 0 ||
                $dims[1] !== null && $dims[1] <= 0 ||
                empty(${$dims[0]}) && empty(${$dims[1]});
            $this->assertTrue($result);
        }
    }

    public function resizeDataProvider()
    {
        return $this->_prepareData(
            [
                [$this->_getFixture('image_adapters_test.png'), [150, 70]],
                [$this->_getFixture('image_adapters_test.png'), [null, 70]],
                [$this->_getFixture('image_adapters_test.png'), [100, null]],
                [$this->_getFixture('image_adapters_test.png'), [null, null]],
                [$this->_getFixture('image_adapters_test.png'), [-100, -50]],
            ]
        );
    }

    /**
     * @param string $image
     * @param int $angle
     * @param array $pixel
     * @param string $adapterType
     *
     * @dataProvider rotateDataProvider
     * @depends testOpen
     */
    public function testRotate($image, $angle, $pixel, $adapterType)
    {
        $adapter = $this->_getAdapter($adapterType);
        $adapter->open($image);

        $size = [$adapter->getOriginalWidth(), $adapter->getOriginalHeight()];

        $colorBefore = $adapter->getColorAt($pixel['x'], $pixel['y']);
        $adapter->rotate($angle);

        $newPixel = $this->_convertCoordinates(
            $pixel,
            $angle,
            $size,
            [$adapter->getOriginalWidth(), $adapter->getOriginalHeight()]
        );
        $colorAfter = $adapter->getColorAt($newPixel['x'], $newPixel['y']);

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
        $angle = $angle * pi() / 180;
        $center = ['x' => $oldSize[0] / 2, 'y' => $oldSize[1] / 2];

        $pixel['x'] -= $center['x'];
        $pixel['y'] -= $center['y'];
        return [
            'x' => round($size[0] / 2 + $pixel['x'] * cos($angle) + $pixel['y'] * sin($angle), 0),
            'y' => round($size[1] / 2 + $pixel['y'] * cos($angle) - $pixel['x'] * sin($angle), 0),
        ];
    }

    public function rotateDataProvider()
    {
        return $this->_prepareData(
            [
                [$this->_getFixture('image_adapters_test.png'), 45, ['x' => 157, 'y' => 35]],
                [$this->_getFixture('image_adapters_test.png'), 48, ['x' => 157, 'y' => 35]],
                [$this->_getFixture('image_adapters_test.png'), 90, ['x' => 250, 'y' => 74]],
                [$this->_getFixture('image_adapters_test.png'), 180, ['x' => 250, 'y' => 74]],
            ]
        );
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
     * @param string $adapterType
     *
     * @dataProvider imageWatermarkDataProvider
     * @depends testOpen
     */
    public function testWatermark(
        $image,
        $watermark,
        $width,
        $height,
        $opacity,
        $position,
        $colorX,
        $colorY,
        $adapterType
    ) {
        $adapter = $this->_getAdapter($adapterType);
        $adapter->open($image);
        $pixel = $this->_prepareColor(['x' => $colorX, 'y' => $colorY], $position, $adapter);

        $colorBefore = $adapter->getColorAt($pixel['x'], $pixel['y']);
        $adapter->setWatermarkWidth(
            $width
        )->setWatermarkHeight(
            $height
        )->setWatermarkImageOpacity(
            $opacity
        )->setWatermarkPosition(
            $position
        )->watermark(
            $watermark
        );
        $colorAfter = $adapter->getColorAt($pixel['x'], $pixel['y']);

        $result = $this->_compareColors($colorBefore, $colorAfter);
        $message = join(',', $colorBefore) . ' not equals ' . join(',', $colorAfter);
        $this->assertFalse($result, $message);
    }

    public function imageWatermarkDataProvider()
    {
        return $this->_prepareData(
            [
                [
                    $this->_getFixture('image_adapters_test.png'),
                    $this->_getFixture('watermark.png'),
                    50,
                    50,
                    100,
                    \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_BOTTOM_RIGHT,
                    10,
                    10,
                ],
                [
                    $this->_getFixture('image_adapters_test.png'),
                    $this->_getFixture('watermark.png'),
                    100,
                    70,
                    100,
                    \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TOP_LEFT,
                    10,
                    10
                ],
                [
                    $this->_getFixture('image_adapters_test.png'),
                    $this->_getFixture('watermark.png'),
                    100,
                    70,
                    100,
                    \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TILE,
                    10,
                    10
                ],
                [
                    $this->_getFixture('image_adapters_test.png'),
                    $this->_getFixture('watermark.png'),
                    100,
                    100,
                    100,
                    \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_STRETCH,
                    10,
                    10
                ],
                [
                    $this->_getFixture('image_adapters_test.png'),
                    $this->_getFixture('watermark.jpg'),
                    50,
                    50,
                    100,
                    \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_BOTTOM_RIGHT,
                    10,
                    10
                ],
                [
                    $this->_getFixture('image_adapters_test.png'),
                    $this->_getFixture('watermark.gif'),
                    50,
                    50,
                    100,
                    \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_BOTTOM_RIGHT,
                    10,
                    10
                ],
            ]
        );
    }

    /**
     * Sets colorX and colorY coordinates according image width and height
     *
     * @param array $pixel ('x' => ..., 'y' => ...)
     * @param string $position
     * @param \Magento\Framework\Image\Adapter\AbstractAdapter $adapter
     * @return array
     */
    protected function _prepareColor($pixel, $position, $adapter)
    {
        switch ($position) {
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_BOTTOM_RIGHT:
                $pixel['x'] = $adapter->getOriginalWidth() - 1;
                $pixel['y'] = $adapter->getOriginalHeight() - 1;
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_BOTTOM_LEFT:
                $pixel['x'] = 1;
                $pixel['y'] = $adapter->getOriginalHeight() - 1;
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TOP_LEFT:
                $pixel['x'] = 1;
                $pixel['y'] = 1;
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TOP_RIGHT:
                $pixel['x'] = $adapter->getOriginalWidth() - 1;
                $pixel['y'] = 1;
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_CENTER:
                $pixel['x'] = $adapter->getOriginalWidth() / 2;
                $pixel['y'] = $adapter->getOriginalHeight() / 2;
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_STRETCH:
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TILE:
                $pixel['x'] = round($adapter->getOriginalWidth() / 3);
                $pixel['y'] = round($adapter->getOriginalHeight() / 3);
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
     * @param string $adapterType
     *
     * @dataProvider cropDataProvider
     * @depends testOpen
     */
    public function testCrop($image, $left, $top, $right, $bottom, $adapterType)
    {
        $adapter = $this->_getAdapter($adapterType);
        $adapter->open($image);

        $expectedSize = [
            $adapter->getOriginalWidth() - $left - $right,
            $adapter->getOriginalHeight() - $top - $bottom,
        ];

        $adapter->crop($top, $left, $right, $bottom);

        $newSize = [$adapter->getOriginalWidth(), $adapter->getOriginalHeight()];

        $this->assertEquals($expectedSize, $newSize);
    }

    public function cropDataProvider()
    {
        return $this->_prepareData(
            [
                [$this->_getFixture('image_adapters_test.png'), 50, 50, 75, 75],
                [$this->_getFixture('image_adapters_test.png'), 20, 50, 35, 35],
                [$this->_getFixture('image_adapters_test.png'), 0, 0, 0, 0],
            ]
        );
    }

    /**
     * @dataProvider createPngFromStringDataProvider
     *
     * @param array $pixel1
     * @param array $expectedColor1
     * @param array $pixel2
     * @param array $expectedColor2
     * @param string $adapterType
     */
    public function testCreatePngFromString($pixel1, $expectedColor1, $pixel2, $expectedColor2, $adapterType)
    {
        if (!function_exists('imagettfbbox')) {
            $this->markTestSkipped('Workaround of problem with imagettfbbox function on Travis');
        }

        $adapter = $this->_getAdapter($adapterType);

        /** @var \Magento\Framework\Filesystem\Directory\ReadFactory readFactory */
        $readFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Filesystem\Directory\ReadFactory'
        );
        $reader = $readFactory->create(BP);
        $path = $reader->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');
        $adapter->createPngFromString('T', $path);
        $adapter->refreshImageDimensions();

        $color1 = $adapter->getColorAt($pixel1['x'], $pixel1['y']);
        $this->assertEquals($expectedColor1, $color1);

        $color2 = $adapter->getColorAt($pixel2['x'], $pixel2['y']);
        $this->assertEquals($expectedColor2, $color2);
    }

    /**
     * We use different points for same cases for different adapters because of different antialiasing behavior
     * @link http://php.net/manual/en/function.imageantialias.php
     * @return array
     */
    public function createPngFromStringDataProvider()
    {
        return [
            [
                ['x' => 5, 'y' => 8],
                'expectedColor1' => ['red' => 0, 'green' => 0, 'blue' => 0, 'alpha' => 0],
                ['x' => 0, 'y' => 15],
                'expectedColor2' => ['red' => 255, 'green' => 255, 'blue' => 255, 'alpha' => 127],
                \Magento\Framework\Image\Adapter\AdapterInterface::ADAPTER_GD2,
            ],
            [
                ['x' => 4, 'y' => 7],
                'expectedColor1' => ['red' => 0, 'green' => 0, 'blue' => 0, 'alpha' => 0],
                ['x' => 0, 'y' => 15],
                'expectedColor2' => ['red' => 255, 'green' => 255, 'blue' => 255, 'alpha' => 127],
                \Magento\Framework\Image\Adapter\AdapterInterface::ADAPTER_IM
            ],
            [
                ['x' => 1, 'y' => 14],
                'expectedColor1' => ['red' => 255, 'green' => 255, 'blue' => 255, 'alpha' => 127],
                ['x' => 5, 'y' => 12],
                'expectedColor2' => ['red' => 0, 'green' => 0, 'blue' => 0, 'alpha' => 0],
                \Magento\Framework\Image\Adapter\AdapterInterface::ADAPTER_GD2
            ],
            [
                ['x' => 1, 'y' => 14],
                'expectedColor1' => ['red' => 255, 'green' => 255, 'blue' => 255, 'alpha' => 127],
                ['x' => 4, 'y' => 10],
                'expectedColor2' => ['red' => 0, 'green' => 0, 'blue' => 0, 'alpha' => 0],
                \Magento\Framework\Image\Adapter\AdapterInterface::ADAPTER_IM
            ]
        ];
    }

    public function testValidateUploadFile()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $imageAdapter = $objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
        $this->assertTrue($imageAdapter->validateUploadFile($this->_getFixture('magento_thumbnail.jpg')));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateUploadFileException()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $imageAdapter = $objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
        $imageAdapter->validateUploadFile(__FILE__);
    }
}
