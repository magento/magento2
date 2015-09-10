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
use Magento\Framework\Phrase\Renderer\Translate;

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
     * @var Translate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translateMock;

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
        $this->translateMock = $this->getMock('Magento\Framework\Phrase\Renderer\Translate', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->rootDirectoryMock);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Translation\Model\Js\DataProvider',
            [
                'appState' => $this->appStateMock,
                'config' => $this->configMock,
                'filesystem' => $filesystem,
                'filesUtility' => $this->filesUtilityMock,
                'translate' => $this->translateMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetData()
    {
        $themePath = 'blank';
        $areaCode = 'adminhtml';

        $filePaths = [['path1'], ['path2'], ['path3'], ['path4']];

        $jsFilesMap = [
            ['base', $themePath, '*', '*', [$filePaths[0]]],
            [$areaCode, $themePath, '*', '*', [$filePaths[1]]]
        ];
        $staticFilesMap = [
            ['base', $themePath, '*', '*', [$filePaths[2]]],
            [$areaCode, $themePath, '*', '*', [$filePaths[3]]]
        ];

        $relativePathMap = [
            ['path1', 'relativePath1'],
            ['path2', 'relativePath2'],
            ['path3', 'relativePath3'],
            ['path4', 'relativePath4']
        ];

        $expectedResult = [
            'hello1' => 'hello1translated',
            'hello2' => 'hello2translated',
            'hello3' => 'hello3translated',
            'hello4' => 'hello4translated'
        ];

        $contentsMap = [
            ['relativePath1', null, null, 'content1$.mage.__("hello1")content1'],
            ['relativePath2', null, null, 'content2$.mage.__("hello2")content2'],
            ['relativePath3', null, null, 'content2$.mage.__("hello3")content3'],
            ['relativePath4', null, null, 'content2$.mage.__("hello4")content4']
        ];

        $translateMap = [
            [['hello1'], [], 'hello1translated'],
            [['hello2'], [], 'hello2translated'],
            [['hello3'], [], 'hello3translated'],
            [['hello4'], [], 'hello4translated']
        ];

        $patterns = ['~\$\.mage\.__\(([\'"])(.+?)\1\)~'];

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);
        $this->filesUtilityMock->expects($this->any())
            ->method('getJsFiles')
            ->willReturnMap($jsFilesMap);
        $this->filesUtilityMock->expects($this->any())
            ->method('getStaticHtmlFiles')
            ->willReturnMap($staticFilesMap);

        $this->rootDirectoryMock->expects($this->any())
            ->method('getRelativePath')
            ->willReturnMap($relativePathMap);
        $this->rootDirectoryMock->expects($this->any())
            ->method('readFile')
            ->willReturnMap($contentsMap);
        $this->configMock->expects($this->any())
            ->method('getPatterns')
            ->willReturn($patterns);
        $this->translateMock->expects($this->any())
            ->method('render')
            ->willReturnMap($translateMap);

        $this->assertEquals($expectedResult, $this->model->getData($themePath));
    }
}
