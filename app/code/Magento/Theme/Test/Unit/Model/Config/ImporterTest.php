<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Config;

use Magento\Theme\Model\Config\Importer;
use Magento\Theme\Model\ResourceModel\Theme as ThemeResourceModel;
use Magento\Theme\Model\ResourceModel\Theme\Data\Collection as ThemeDbCollection;
use Magento\Theme\Model\ResourceModel\Theme\Data\CollectionFactory;
use Magento\Theme\Model\Theme\Collection as ThemeFilesystemCollection;
use Magento\Theme\Model\Theme\Data;
use Magento\Theme\Model\Theme\Registration;

class ImporterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ThemeFilesystemCollection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeFilesystemCollectionMock;

    /**
     * @var ThemeDbCollection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeDbCollectionMock;

    /**
     * @var CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themecollectionFactoryMock;

    /**
     * @var Registration|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeRegistrationMock;

    /**
     * @var ThemeResourceModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeResourceModelMock;

    /**
     * @var Importer
     */
    private $importer;

    protected function setUp(): void
    {
        $this->themeFilesystemCollectionMock = $this->getMockBuilder(ThemeFilesystemCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeDbCollectionMock = $this->getMockBuilder(ThemeDbCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themecollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
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
     */
    public function testImportWithException()
    {
        $this->expectException(\Magento\Framework\Exception\State\InvalidTransitionException::class);
        $this->expectExceptionMessage('Some error');

        $this->themeRegistrationMock->expects($this->once())
            ->method('register')
            ->willThrowException(new \Exception('Some error'));

        $this->importer->import([]);
    }

    public function testImport()
    {
        /** @var Data|\PHPUnit\Framework\MockObject\MockObject $firstThemeMock */
        $firstThemeMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $firstThemeMock->expects($this->atLeastOnce())
            ->method('getFullPath')
            ->willReturn('frontend/Magento/luma');
        /** @var Data|\PHPUnit\Framework\MockObject\MockObject $secondThemeMock */
        $secondThemeMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $secondThemeMock->expects($this->once())
            ->method('getFullPath')
            ->willReturn('frontend/Magento/blank');
        /** @var Data|\PHPUnit\Framework\MockObject\MockObject $thirdThemeMock */
        $thirdThemeMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $thirdThemeMock->expects($this->once())
            ->method('getFullPath')
            ->willReturn('frontend/Magento/test');

        $this->themeRegistrationMock->expects($this->once())
            ->method('register')
            ->willReturnSelf();
        $this->themeDbCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$firstThemeMock, $secondThemeMock, $thirdThemeMock]);
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
                ['frontend/Magento/blank', $secondThemeMock],
            ]);
        $this->themeFilesystemCollectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn(['frontend/Magento/luma']);

        $result = $this->importer->import([
            'frontend/Magento/test' => [
                'area' => 'frontend',
                'parent_id' => 'Magento/blank',
                'theme_path' => 'Magento/test',
            ],
        ]);

        $this->assertSame(
            [
                '<info>Theme import was started.</info>',
                '<info>Theme import finished.</info>'
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
            /** @var Data|\PHPUnit\Framework\MockObject\MockObject $themeMock */
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

        $this->assertEquals($expectedResult, $this->importer->getWarningMessages($inFile));
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
                []
            ],
            [
                [
                    'frontend/Magento/luma' => ['Data of theme'],
                    'frontend/Magento/blank' => ['Data of theme']
                ],
                [],
                ['frontend/Magento/luma', 'frontend/Magento/blank'],
                [
                    '<info>The following themes will be registered:</info>'
                    . ' frontend/Magento/luma, frontend/Magento/blank',
                ]
            ],
            [
                [
                    'frontend/Magento/luma' => ['Data of theme'],
                    'frontend/Magento/blank' => ['Data of theme']
                ],
                [],
                [],
                []
            ],
            [
                [],
                [],
                ['frontend/Magento/luma'],
                [
                    '<info>The following themes will be registered:</info> frontend/Magento/luma',
                ]
            ],
            [
                [],
                ['frontend/Magento/luma', 'frontend/Magento/blank'],
                [],
                [
                    '<info>The following themes will be removed:</info> frontend/Magento/luma, frontend/Magento/blank',
                ]
            ],
            [
                [],
                ['frontend/Magento/luma'],
                ['frontend/Magento/luma'],
                []
            ],
        ];
    }
}
