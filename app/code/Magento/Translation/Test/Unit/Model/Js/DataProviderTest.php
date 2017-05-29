<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Js;

use Magento\Framework\App\State;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\ReadInterface;
use Magento\Translation\Model\Js\DataProvider;
use Magento\Translation\Model\Js\Config;
use Magento\Framework\Phrase\Renderer\Translate;

/**
 * Class DataProviderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    protected $fileReadMock;

    /**
     * @var Translate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translateMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->appStateMock = $this->getMock(\Magento\Framework\App\State::class, [], [], '', false);
        $this->configMock = $this->getMock(\Magento\Translation\Model\Js\Config::class, [], [], '', false);
        $this->filesUtilityMock = $this->getMock(\Magento\Framework\App\Utility\Files::class, [], [], '', false);
        $fileReadFactory = $this->getMock(\Magento\Framework\Filesystem\File\ReadFactory::class, [], [], '', false);
        $this->fileReadMock = $this->getMock(\Magento\Framework\Filesystem\File\Read::class, [], [], '', false);
        $this->translateMock = $this->getMock(\Magento\Framework\Phrase\Renderer\Translate::class, [], [], '', false);
        $fileReadFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->fileReadMock);
        $dirSearch = $this->getMock(\Magento\Framework\Component\DirSearch::class, [], [], '', false);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Translation\Model\Js\DataProvider::class,
            [
                'appState' => $this->appStateMock,
                'config' => $this->configMock,
                'fileReadFactory' => $fileReadFactory,
                'translate' => $this->translateMock,
                'dirSearch' => $dirSearch,
                'filesUtility' => $this->filesUtilityMock,
                'componentRegistrar' =>
                    $this->getMock(\Magento\Framework\Component\ComponentRegistrar::class, [], [], '', false)
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

        $expectedResult = [
            'hello1' => 'hello1translated',
            'hello2' => 'hello2translated',
            'hello3' => 'hello3translated',
            'hello4' => 'hello4translated'
        ];

        $contentsMap = [
            'content1$.mage.__("hello1")content1',
            'content2$.mage.__("hello2")content2',
            'content2$.mage.__("hello3")content3',
            'content2$.mage.__("hello4")content4'
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

        foreach ($contentsMap as $index => $content) {
            $this->fileReadMock->expects($this->at($index))
                ->method('readAll')
                ->willReturn($content);
        }

        $this->configMock->expects($this->any())
            ->method('getPatterns')
            ->willReturn($patterns);
        $this->translateMock->expects($this->any())
            ->method('render')
            ->willReturnMap($translateMap);

        $this->assertEquals($expectedResult, $this->model->getData($themePath));
    }
}
