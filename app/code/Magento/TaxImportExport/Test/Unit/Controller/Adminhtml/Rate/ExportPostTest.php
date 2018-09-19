<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TaxImportExport\Test\Unit\Controller\Adminhtml\Rate;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportPostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TaxImportExport\Controller\Adminhtml\Rate\ExportPost
     */
    private $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->fileFactoryMock = $this->createMock(\Magento\Framework\App\Response\Http\FileFactory::class);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->controller = $this->objectManagerHelper->getObject(
            \Magento\TaxImportExport\Controller\Adminhtml\Rate\ExportPost::class,
            [
                'fileFactory' => $this->fileFactoryMock,
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testExecute()
    {
        $headers = new \Magento\Framework\DataObject(
            [
                'code' => __('Code'),
                'country_name' => __('Country'),
                'region_name' => __('State'),
                'tax_postcode' => __('Zip/Post Code'),
                'rate' => __('Rate'),
                'zip_is_range' => __('Zip/Post is Range'),
                'zip_from' => __('Range From'),
                'zip_to' => __('Range To'),
            ]
        );
        $template = '"{{code}}","{{country_name}}","{{region_name}}","{{tax_postcode}}","{{rate}}"' .
            ',"{{zip_is_range}}","{{zip_from}}","{{zip_to}}"';
        $content = $headers->toString($template);
        $content .= "\n";
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeCollectionMock = $this->objectManagerHelper->getCollectionMock(
            \Magento\Store\Model\ResourceModel\Store\Collection::class,
            []
        );
        $rateCollectionMock = $this->objectManagerHelper->getCollectionMock(
            \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection::class,
            []
        );

        $taxCollectionMock = $this->objectManagerHelper->getCollectionMock(
            \Magento\Tax\Model\ResourceModel\Calculation\Rate\Title\Collection::class,
            []
        );
        $storeCollectionMock->expects($this->once())->method('setLoadDefault')->willReturnSelf();
        $rateTitleMock = $this->createMock(\Magento\Tax\Model\Calculation\Rate\Title::class);
        $rateTitleMock->expects($this->once())->method('getCollection')->willReturn($taxCollectionMock);
        $storeMock->expects($this->once())->method('getCollection')->willReturn($storeCollectionMock);
        $this->objectManagerMock->expects($this->any())->method('create')->willReturnMap([
            [\Magento\Store\Model\Store::class, [], $storeMock],
            [\Magento\Tax\Model\Calculation\Rate\Title::class, [], $rateTitleMock],
            [\Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection::class, [], $rateCollectionMock]
        ]);
        $rateCollectionMock->expects($this->once())->method('joinCountryTable')->willReturnSelf();
        $rateCollectionMock->expects($this->once())->method('joinRegionTable')->willReturnSelf();
        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with('tax_rates.csv', $content, DirectoryList::VAR_DIR);
        $this->controller->execute();
    }
}
