<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Config;

use Magento\Theme\Model\Config\Importer;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme\Collection as ThemeFilesystemCollection;
use Magento\Theme\Model\ResourceModel\Theme\Data\CollectionFactory;
use Magento\Theme\Model\ResourceModel\Theme\Data\Collection as ThemeDbCollection;
use Magento\Theme\Model\Theme\Registration;
use Magento\Theme\Model\Theme\Data;
use Magento\Theme\Model\ResourceModel\Theme as ThemeResourceModel;

class ImporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThemeFilesystemCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeFilesystemCollectionMock;

    /**
     * @var ThemeDbCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeDbCollectionMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themecollectionFactoryMock;

    /**
     * @var Registration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeRegistrationMock;

    /**
     * @var ThemeResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeResourceModelMock;

    /**
     * @var Importer
     */
    private $importer;

    protected function setUp()
    {
        $this->themeFilesystemCollectionMock = $this->getMockBuilder(ThemeFilesystemCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeDbCollectionMock = $this->getMockBuilder(ThemeDbCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themecollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeRegistrationMock = $this->getMockBuilder(Registration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeResourceModelMock = $this->getMockBuilder(ThemeResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->importer = new Importer(
            $this->themeFilesystemCollectionMock,
            $this->themecollectionFactoryMock,
            $this->themeRegistrationMock,
            $this->themeResourceModelMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Some error
     */
    public function testImportWithException()
    {
        $this->themeRegistrationMock->expects($this->once())
            ->method('register')
            ->willThrowException(new \Exception('Some error'));

        $this->importer->import([]);
    }

    public function testImport()
    {
        /** @var Data|\PHPUnit_Framework_MockObject_MockObject $firstThemeMock */
        $firstThemeMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $firstThemeMock->expects($this->atLeastOnce())
            ->method('getFullPath')
            ->willReturn('frontend/Magento/luma');
        $firstThemeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        /** @var Data|\PHPUnit_Framework_MockObject_MockObject $secondThemeMock */
        $secondThemeMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $secondThemeMock->expects($this->once())
            ->method('getFullPath')
            ->willReturn('frontend/Magento/blank');
        /** @var Data|\PHPUnit_Framework_MockObject_MockObject $thirdThemeMock */
        $thirdThemeMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['setType', 'setData', 'getId'])
            ->getMock();
        $thirdThemeMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $thirdThemeMock->expects($this->once())
            ->method('setData')
            ->with(['area' => 'adminhtml', 'theme_path' => 'Magento/admin'])
            ->willReturnSelf();
        $thirdThemeMock->expects($this->once())
            ->method('setType')
            ->with(ThemeInterface::TYPE_VIRTUAL)
            ->willReturnSelf();

        $this->themeRegistrationMock->expects($this->once())
            ->method('register')
            ->willReturnSelf();
        $this->themeDbCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$firstThemeMock, $secondThemeMock]);
        $this->themecollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->themeDbCollectionMock);
        $this->themeResourceModelMock->expects($this->once())
            ->method('delete')
            ->with($secondThemeMock)
            ->willReturnSelf();
        $this->themeRegistrationMock->expects($this->any())
            ->method('getThemeFromDb')
            ->willReturnMap([
                ['frontend/Magento/luma', $firstThemeMock],
                ['adminhtml/Magento/admin', $thirdThemeMock],
            ]);
        $this->themeResourceModelMock->expects($this->once())
            ->method('save')
            ->with($thirdThemeMock)
            ->willReturnSelf();

        $result = $this->importer->import([
            'frontend/Magento/luma' => ['area' => 'frontend', 'theme_path' => 'Magento/luma'],
            'adminhtml/Magento/admin' => ['area' => 'adminhtml', 'theme_path' => 'Magento/admin'],
        ]);

        $this->assertSame(
            [
                '<info>Theme import was started.</info>',
                '<info>Theme import was finished.</info>'
            ],
            $result
        );
    }

    /**
     * @param array $inFile
     * @param array $inDb
     * @param array $inFs
     * @param array $expectedResult
     * @dataProvider getWarningMessagesDataProvider
     */
    public function testGetWarningMessages(array $inFile, array $inDb, array $inFs, array $expectedResult)
    {
        $themes = [];
        foreach ($inDb as $themePath) {
            /** @var Data|\PHPUnit_Framework_MockObject_MockObject $themeMock */
            $themeMock = $this->getMockBuilder(Data::class)
                ->disableOriginalConstructor()
                ->getMock();
            $themeMock->expects($this->any())
                ->method('getFullPath')
                ->willReturn($themePath);
            $themes[] = $themeMock;
        }

        $this->themeDbCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn($themes);
        $this->themecollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->themeDbCollectionMock);
        $this->themeFilesystemCollectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($inFs);

        $this->assertSame($expectedResult, $this->importer->getWarningMessages($inFile));
    }

    /**
     * @return array
     */
    public function getWarningMessagesDataProvider()
    {
        return [
            [[], [], [], []],
            [
                ['frontend/Magento/luma' => ['Data of theme']],
                ['frontend/Magento/luma'],
                ['frontend/Magento/luma'],
                []
            ],
            [
                ['frontend/Magento/luma' => ['Data of theme']],
                ['frontend/Magento/luma'],
                [],
                [
                    '<info>As result of themes importing you will get:</info>',
                    '<info>The following themes will be virtual:</info>',
                    'frontend/Magento/luma',
                ]
            ],
            [
                ['frontend/Magento/luma' => ['Data of theme']],
                [],
                ['frontend/Magento/luma'],
                [
                    '<info>As result of themes importing you will get:</info>',
                    '<info>The following themes will be registered:</info>',
                    'frontend/Magento/luma',
                ]
            ],
            [
                ['frontend/Magento/luma' => ['Data of theme']],
                [],
                [],
                [
                    '<info>As result of themes importing you will get:</info>',
                    '<info>The following themes will be registered:</info>',
                    'frontend/Magento/luma',
                    '<info>The following themes will be virtual:</info>',
                    'frontend/Magento/luma',
                ]
            ],
            [
                [],
                [],
                ['frontend/Magento/luma'],
                []
            ],
            [
                [],
                ['frontend/Magento/luma'],
                [],
                [
                    '<info>As result of themes importing you will get:</info>',
                    '<info>The following themes will be removed:</info>',
                    'frontend/Magento/luma',
                ]
            ],
            [
                [],
                ['frontend/Magento/luma'],
                ['frontend/Magento/luma'],
                [
                    '<info>As result of themes importing you will get:</info>',
                    '<info>The following themes will be removed:</info>',
                    'frontend/Magento/luma',
                ]
            ],
        ];
    }
}
