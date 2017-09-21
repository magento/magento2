<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale\Test\Unit\Deployed;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Locale\Deployed\Codes;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\ThemeInterface;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for Codes class.
 *
 * @see Codes
 */
class CodesTest extends \PHPUnit\Framework\TestCase
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
    protected function setUp()
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

        $themeMock = $this->getMockBuilder(ThemeInterface::class)
            ->getMockForAbstractClass();
        $themeMock->expects($this->once())
            ->method('getFullPath')
            ->willReturn($fullPath);
        $this->flyweightFactoryMock->expects($this->once())
            ->method('create')
            ->with($code, $area)
            ->willReturn($themeMock);
        $reader = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
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
