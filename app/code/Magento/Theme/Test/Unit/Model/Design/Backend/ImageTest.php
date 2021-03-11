<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Theme\Model\Design\Backend\Image;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageTest extends \PHPUnit\Framework\TestCase
{
    /** @var Image */
    private $imageBackend;

    /** @var IoFile */
    private $ioFileSystem;

    /**
     * @var ReadFactory||\PHPUnit_Framework_MockObject_MockObject
     */
    private $tmpDirectory;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->ioFileSystem = $this->getMockObject(IoFile::class);
        $this->tmpDirectory = $this->getMockObject(ReadFactory::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->imageBackend = $objectManagerHelper->getObject(Image::class, [
            'ioFileSystem' => $this->ioFileSystem,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        unset($this->imageBackend);
    }

    /**
     * @param string $class
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockObject($class, $methods = [])
    {
        $builder =  $this->getMockBuilder($class)
            ->disableOriginalConstructor();
        if (count($methods)) {
            $builder->setMethods($methods);
        }
        return  $builder->getMock();
    }

    /**
     * Test for beforeSave method with invalid file extension.
     */
    public function testBeforeSaveWithInvalidExtensionFile()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Invalid file provided.');

        $invalidFileName = 'fileName.invalidExtension';
        $this->imageBackend->setData(
            [
                'value' => [
                    [
                        'file' => $invalidFileName,
                    ]
                ],
            ]
        );
        $expectedPathInfo = [
            'extension' => 'invalidExtension'
        ];
        $this->ioFileSystem
            ->expects($this->any())
            ->method('getPathInfo')
            ->with($invalidFileName)
            ->willReturn($expectedPathInfo);
        $this->imageBackend->beforeSave();
    }
}
