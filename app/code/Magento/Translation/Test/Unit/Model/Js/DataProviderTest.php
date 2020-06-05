<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Model\Js;

use Magento\Framework\App\State;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Filesystem\File\Read;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Filesystem\File\ReadInterface;
use Magento\Framework\Phrase\Renderer\Translate;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Translation\Model\Js\Config;
use Magento\Translation\Model\Js\DataProvider as ModelDataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify data provider translation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends TestCase
{
    /**
     * @var ModelDataProvider
     */
    private $model;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Files|MockObject
     */
    private $filesUtilityMock;

    /**
     * @var ReadInterface|MockObject
     */
    private $fileReadMock;

    /**
     * @var Translate|MockObject
     */
    private $translateMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->appStateMock = $this->createMock(State::class);
        $this->configMock = $this->createMock(Config::class);
        $this->filesUtilityMock = $this->createMock(Files::class);
        $fileReadFactory = $this->createMock(ReadFactory::class);
        $this->fileReadMock = $this->createMock(Read::class);
        $this->translateMock = $this->createMock(Translate::class);
        $fileReadFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->fileReadMock);
        $dirSearch = $this->createMock(DirSearch::class);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            ModelDataProvider::class,
            [
                'appState' => $this->appStateMock,
                'config' => $this->configMock,
                'fileReadFactory' => $fileReadFactory,
                'translate' => $this->translateMock,
                'dirSearch' => $dirSearch,
                'filesUtility' => $this->filesUtilityMock,
                'componentRegistrar' => $this->createMock(ComponentRegistrar::class)
            ]
        );
    }

    /**
     * Verify data translate.
     *
     * @param array $config
     * @return void
     * @dataProvider configDataProvider
     */
    public function testGetData(array $config): void
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

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);
        $this->filesUtilityMock->expects($this->any())
            ->method('getJsFiles')
            ->willReturnMap($jsFilesMap);
        $this->filesUtilityMock->expects($this->any())
            ->method('getStaticHtmlFiles')
            ->willReturnMap($staticFilesMap);

        foreach ($config['contentsMap'] as $index => $content) {
            $this->fileReadMock->expects($this->at($index))
                ->method('readAll')
                ->willReturn($content);
        }

        $this->configMock->expects($this->any())
            ->method('getPatterns')
            ->willReturn($config['patterns']);
        $this->translateMock->expects($this->any())
            ->method('render')
            ->willReturnMap($config['translateMap']);

        $actualResult = $this->model->getData($themePath);
        $this->assertEquals($config['expectedResult'], $actualResult);
        $this->assertEquals(
            json_encode($config['expectedResult']),
            json_encode($actualResult),
            "Translations should be sorted by key"
        );
    }

    /**
     * Verify get data throwing exception.
     *
     * @param array $config
     * @return void
     * @dataProvider configDataProvider
     */
    public function testGetDataThrowingException(array $config): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $themePath = 'blank';
        $areaCode = 'adminhtml';
        $patterns = $config['patterns'];

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

    /**
     * Config data provider.
     *
     * @return array
     */
    public function configDataProvider(): array
    {
        return [
            [
                [
                    'patterns' => [
                        '~\$\.mage\.__\(([\'"])(.+?)\1\)~',
                        '~(?:i18n\:|_\.i18n\()\s*(["\'])(.*?)(?<!\\\\)\1~',
                        '~translate\=("\')([^\'].*?)\'\"~',
                        '~(?s)\$t\(\s*([\'"])(\?\<translate\>.+?)(?<!\\\)\1\s*(*SKIP)\)(?s)~',
                        '~translate args\=("|\'|"\'|\\\"\')([^\'].*?)(\'\\\"|\'"|\'|")~',
                    ],
                    'expectedResult' => [
                        'hello1' => 'hello1translated',
                        'hello2' => 'hello2translated',
                        'hello3' => 'hello3translated',
                        'hello4' => 'hello4translated',
                        'ko i18' => 'ko i18 translated',
                        'underscore i18' => 'underscore i18 translated',
                    ],
                    'contentsMap' => [
                        'content1$.mage.__("hello1")content1',
                        'content2$.mage.__("hello2")content2',
                        'content2$.mage.__("hello4")content4 <!-- ko i18n: "ko i18" --><!-- /ko -->',
                        'content2$.mage.__("hello3")content3 <% _.i18n("underscore i18") %>',
                    ],
                    'translateMap' => [
                        [['hello1'], [], 'hello1translated'],
                        [['hello2'], [], 'hello2translated'],
                        [['hello3'], [], 'hello3translated'],
                        [['hello4'], [], 'hello4translated'],
                        [['ko i18'], [], 'ko i18 translated'],
                        [['underscore i18'], [], 'underscore i18 translated'],
                    ]
                ],
            ]
        ];
    }
}
