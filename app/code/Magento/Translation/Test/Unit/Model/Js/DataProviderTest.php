<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Js;

use Magento\Framework\App\State;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Translation\Model\Js\DataProvider;
use Magento\Translation\Model\Js\Config;

/**
 * Class DataProviderTest
 */
class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataProvider
     */
    protected $model;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appStateMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var Files|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesUtilityMock;

    /**
     * @var ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectoryMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->appStateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->configMock = $this->getMock('Magento\Translation\Model\Js\Config', [], [], '', false);
        $this->filesUtilityMock = $this->getMock('Magento\Framework\App\Utility\Files', [], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->rootDirectoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->rootDirectoryMock);
        $this->model = new DataProvider(
            $this->appStateMock,
            $this->configMock,
            $filesystem,
            $this->filesUtilityMock
        );
    }

    /**
     * @return void
     */
    public function testGetData()
    {
        $themePath = 'blank';
        $areaCode = 'adminhtml';
        $files = [['path1'], ['path2']];

        $relativePathMap = [
            ['path1' => 'relativePath1'],
            ['path2' => 'relativePath2']
        ];
        $contentsMap = [
            ['relativePath1' => 'content1$.mage.__("hello1")content1'],
            ['relativePath2' => 'content2$.mage.__("hello2")content2']
        ];

        $patterns = ['~\$\.mage\.__\([\'|\"](.+?)[\'|\"]\)~'];

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);
        $this->filesUtilityMock->expects($this->once())
            ->method('getJsFiles')
            ->with($areaCode, $themePath)
            ->willReturn($files);

        $this->rootDirectoryMock->expects($this->any())
            ->method('getRelativePath')
            ->willReturnMap($relativePathMap);
        $this->rootDirectoryMock->expects($this->any())
            ->method('readFile')
            ->willReturnMap($contentsMap);
        $this->configMock->expects($this->any())
            ->method('getPatterns')
            ->willReturn($patterns);

        $this->assertEquals([], $this->model->getData($themePath));
    }
}
