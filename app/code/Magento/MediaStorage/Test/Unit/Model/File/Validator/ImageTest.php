<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Model\File\Validator;

use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Image as FrameworkImage;
use Magento\Framework\Image\Factory;
use Magento\MediaStorage\Model\File\Validator\Image;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** Unit tests for \Magento\MediaStorage\Model\File\Validator\Image class */
class ImageTest extends TestCase
{
    /**
     * @var Mime|MockObject
     */
    private $fileMimeMock;

    /**
     * @var Factory|MockObject
     */
    private $imageFactoryMock;

    /**
     * @var FrameworkImage|MockObject
     */
    private $imageMock;

    /**
     * @var File|MockObject
     */
    private $fileMock;

    /**
     * @var Image
     */
    private $image;

    protected function setUp(): void
    {
        $this->fileMimeMock = $this->createMock(Mime::class);
        $this->imageFactoryMock = $this->createMock(Factory::class);
        $this->fileMock = $this->createMock(File::class);
        $this->imageMock = $this->createMock(FrameworkImage::class);

        $this->image = new Image(
            $this->fileMimeMock,
            $this->imageFactoryMock,
            $this->fileMock
        );
    }

    /**
     * @dataProvider dataProviderForIsValid
     */
    public function testIsValid($filePath, $mimeType, $result): void
    {
        $this->fileMimeMock->expects($this->once())
            ->method('getMimeType')
            ->with($filePath)
            ->willReturn($mimeType);
        $this->imageMock->expects($this->once())
                ->method('open')
                ->willReturn(null);
        $this->imageFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($this->imageMock);
        $this->assertEquals($result, $this->image->isValid($filePath));
    }

    /**
     * @return array[]
     */
    public static function dataProviderForIsValid()
    {
        return [
            'x-icon' => [dirname(__FILE__) . '/_files/favicon-x-icon.ico',
                'image/x-icon', true],
            'vnd-microsoft-icon' => [dirname(__FILE__) . '/_files/favicon-vnd-microsoft.ico',
                'image/vnd.microsoft.icon', true]
        ];
    }
}
