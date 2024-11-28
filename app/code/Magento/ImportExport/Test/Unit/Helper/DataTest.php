<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\File\Size as FileSize;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Helper\Data as HelperData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class to cover Data Helper
 *
 * Class \Magento\ImportExport\Test\Unit\Helper\DataTest
 */
class DataTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var FileSize|MockObject
     */
    private $fileSizeMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var HelperData|MockObject
     */
    private $helperData;

    /**
     * Set up environment
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->fileSizeMock = $this->createMock(FileSize::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->contextMock->expects($this->any())->method('getScopeConfig')->willReturn($this->scopeConfigMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->helperData = $this->objectManagerHelper->getObject(
            HelperData::class,
            [
                'context' => $this->contextMock,
                'fileSize' => $this->fileSizeMock
            ]
        );
    }

    /**
     * Test getMaxUploadSizeMessage() with data provider below
     *
     * @param float $maxImageSize
     * @param string $expected
     * @return void
     * @dataProvider getMaxUploadSizeMessageDataProvider
     */
    public function testGetMaxUploadSizeMessage($maxImageSize, $expected)
    {
        $this->fileSizeMock->expects($this->any())->method('getMaxFileSizeInMb')->willReturn($maxImageSize);
        $this->assertEquals($expected, $this->helperData->getMaxUploadSizeMessage());
    }

    /**
     * DataProvider for testGetMaxUploadSizeMessage() function
     *
     * @return array
     */
    public static function getMaxUploadSizeMessageDataProvider()
    {
        return [
            'Test with max image size = 10Mb' => [
                'maxImageSize' => 10,
                'expected' => 'Make sure your file isn\'t more than 10M.',
            ],
            'Test with max image size = 0' => [
                'maxImageSize' => 0,
                'expected' => 'We can\'t provide the upload settings right now.',
            ]
        ];
    }

    /**
     * Test getLocalValidPaths()
     *
     * @return void
     */
    public function testGetLocalValidPaths()
    {
        $paths = [
            'available' => [
                'export_xml' => 'var/export/*/*.xml',
                'export_csv' => 'var/export/*/*.csv',
                'import_xml' => 'var/import/*/*.xml',
                'import_csv' => 'var/import/*/*.csv',
            ]
        ];
        $this->scopeConfigMock->expects($this->any())->method('getValue')
            ->with(HelperData::XML_PATH_EXPORT_LOCAL_VALID_PATH)
            ->willReturn($paths);

        $this->assertEquals($paths, $this->helperData->getLocalValidPaths());
    }

    /**
     * Test getBunchSize()
     *
     * @return void
     */
    public function testGetBunchSize()
    {
        $bunchSize = '100';

        $this->scopeConfigMock->expects($this->any())->method('getValue')
            ->with(HelperData::XML_PATH_BUNCH_SIZE)
            ->willReturn($bunchSize);

        $this->assertEquals(100, $this->helperData->getBunchSize());
    }
}
