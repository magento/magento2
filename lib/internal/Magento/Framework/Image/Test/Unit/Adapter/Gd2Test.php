<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Image\Test\Unit\Adapter;

use Magento\Framework\Image\Adapter\AbstractAdapter;
use Magento\Framework\Image\Adapter\Gd2;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * \Magento\Framework\Image\Adapter\Gd2 class test
 */
class Gd2Test extends TestCase
{
    /**
     * Value to mock ini_get('memory_limit')
     *
     * @var string
     */
    public static $memoryLimit;

    /**
     * @var array simulation of getimagesize()
     */
    public static $imageData = [];

    /**
     * Simulation of filesize() function
     *
     * @var int
     */
    public static $imageSize = 1;

    /**
     * Adapter for testing
     * @var Gd2
     */
    protected $adapter;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup testing object
     */
    protected function setUp(): void
    {
        require_once __DIR__ . '/_files/global_php_mock.php';
        $this->objectManager = new ObjectManager($this);
        $this->adapter = $this->objectManager->getObject(Gd2::class);
    }

    /**
     * Test parent class
     */
    public function testParentClass()
    {
        $this->assertInstanceOf(AbstractAdapter::class, $this->adapter);
    }

    /**
     * Test open() method
     *
     * @param array $fileData
     * @param string|bool|null $exception
     * @param string $limit
     * @dataProvider filesProvider
     */
    public function testOpen($fileData, $exception, $limit)
    {
        self::$memoryLimit = $limit;
        self::$imageData = $fileData;

        if (!empty($exception)) {
            $this->expectException($exception);
        }

        $this->adapter->open('file');
    }

    /**
     * @return array
     */
    public static function filesProvider()
    {
        $smallFile = [
            0 => 480,
            1 => 320,
            2 => 2,
            3 => 'width="480" height="320"',
            'bits' => 8,
            'channels' => 3,
            'mime' => 'image/jpeg',
        ];

        $bigFile = [
            0 => 3579,
            1 => 2398,
            2 => 2,
            3 => 'width="3579" height="2398"',
            'bits' => 8,
            'channels' => 3,
            'mime' => 'image/jpeg',
        ];

        return [
            'positive_M' => [$smallFile, false, '2M'],
            'positive_KB' => [$smallFile, false, '2048K'],
            'negative_KB' => [$bigFile, 'OverflowException', '2048K'],
            'negative_bytes' => [$bigFile, 'OverflowException', '2048000'],
            'no_limit' => [$bigFile, false, '-1'],
        ];
    }

    /**
     * Test if open() method resets cached fileType
     */
    public function testOpenDifferentTypes()
    {
        self::$imageData = [
            0 => 480,
            1 => 320,
            2 => 2,
            3 => 'width="480" height="320"',
            'bits' => 8,
            'channels' => 3,
            'mime' => 'image/jpeg',
        ];

        $this->adapter->open('file');
        $type1 = $this->adapter->getImageType();

        self::$imageData = [
            0 => 480,
            1 => 320,
            2 => 3,
            3 => 'width="480" height="320"',
            'bits' => 8,
            'channels' => 3,
            'mime' => 'image/png',
        ];

        $this->adapter->open('file');
        $type2 = $this->adapter->getImageType();

        $this->assertNotEquals($type1, $type2);
    }

    /**
     * Test open() with invalid URL.
     */
    public function testOpenInvalidURL()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->adapter->open('bar://foo.bar');
    }
}
