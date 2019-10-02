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
class DataProviderTest extends \PHPUnit\Framework\TestCase
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
        $this->appStateMock = $this->createMock(\Magento\Framework\App\State::class);
        $this->configMock = $this->createMock(\Magento\Translation\Model\Js\Config::class);
        $this->filesUtilityMock = $this->createMock(\Magento\Framework\App\Utility\Files::class);
        $fileReadFactory = $this->createMock(\Magento\Framework\Filesystem\File\ReadFactory::class);
        $this->fileReadMock = $this->createMock(\Magento\Framework\Filesystem\File\Read::class);
        $this->translateMock = $this->createMock(\Magento\Framework\Phrase\Renderer\Translate::class);
        $fileReadFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->fileReadMock);
        $dirSearch = $this->createMock(\Magento\Framework\Component\DirSearch::class);
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
                    $this->createMock(\Magento\Framework\Component\ComponentRegistrar::class)
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

        $filePaths = [['path1'], ['path2'], ['path4'], ['path3']];

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
            'content2$.mage.__("hello4")content4', // this value should be last after running data provider
            'content2$.mage.__("hello3")content3',
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

        $actualResult = $this->model->getData($themePath);
        $this->assertEquals($expectedResult, $actualResult);
        $this->assertEquals(
            json_encode($expectedResult),
            json_encode($actualResult),
            "Translations should be sorted by key"
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetDataThrowingException()
    {
        $themePath = 'blank';
        $areaCode = 'adminhtml';

        $patterns = ['~\$\.mage\.__\(([\'"])(.+?)\1\)~'];

        $this->fileReadMock->expects($this->once())
            ->method('readAll')
            ->willReturn('content1$.mage.__("hello1")content1');

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);
        $this->filesUtilityMock->expects($this->any())
            ->method('getJsFiles')
            ->willReturn(['test']);
        $this->filesUtilityMock->expects($this->any())
            ->method('getStaticHtmlFiles')
            ->willReturn(['test']);

        $this->configMock->expects($this->any())
            ->method('getPatterns')
            ->willReturn($patterns);

        $this->translateMock->expects($this->once())
            ->method('render')
            ->willThrowException(new \Exception('Test exception'));

        $this->model->getData($themePath);
    }
}
