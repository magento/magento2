<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Locale\Test\Unit\Deployed;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Locale\Deployed\Codes;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\ThemeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Codes class.
 *
 * @see Codes
 */
class CodesTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * @var FlyweightFactory|MockObject
     */
    private $flyweightFactoryMock;

    /**
     * @var Codes
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fileSystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flyweightFactoryMock = $this->getMockBuilder(FlyweightFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Codes(
            $this->flyweightFactoryMock,
            $this->fileSystemMock
        );
    }

    public function testGetList()
    {
        $code = 'code';
        $area = 'area';
        $fullPath = 'some/full/path';

        $themeMock = $this->createMock(ThemeInterface::class);
        $themeMock->expects($this->once())
            ->method('getFullPath')
            ->willReturn($fullPath);
        $this->flyweightFactoryMock->expects($this->once())
            ->method('create')
            ->with($code, $area)
            ->willReturn($themeMock);
        $reader = $this->createMock(ReadInterface::class);
        $reader->expects($this->once())
            ->method('read')
            ->with($fullPath)
            ->willReturn([
                $fullPath . '/de_DE',
                $fullPath . '/en_US',
                $fullPath . '/fr_FR'
            ]);
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($reader);

        $this->assertEquals(
            [
                'de_DE',
                'en_US',
                'fr_FR'
            ],
            $this->model->getList($code, $area)
        );
    }
}
