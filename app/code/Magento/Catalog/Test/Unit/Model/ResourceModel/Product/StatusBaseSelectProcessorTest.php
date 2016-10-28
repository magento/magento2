<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Product\StatusBaseSelectProcessor;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AttributeInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StatusBaseSelectProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * @var StoreResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeResolver;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var StatusBaseSelectProcessor
     */
    private $statusBaseSelectProcessor;

    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->storeResolver = $this->getMockBuilder(StoreResolverInterface::class)->getMock();
        $this->select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();

        $this->statusBaseSelectProcessor =  (new ObjectManager($this))->getObject(StatusBaseSelectProcessor::class, [
            'eavConfig' => $this->eavConfig,
            'storeResolver' => $this->storeResolver,
        ]);
    }

    public function testProcess()
    {
        $backendTable = 'backend_table';
        $attributeId = 2;
        $currentStoreId = 1;

        $statusAttribute = $this->getMockBuilder(AttributeInterface::class)
            ->setMethods(['getBackendTable', 'getAttributeId'])
            ->getMock();
        $statusAttribute->expects($this->atLeastOnce())
            ->method('getBackendTable')
            ->willReturn($backendTable);
        $statusAttribute->expects($this->atLeastOnce())
            ->method('getAttributeId')
            ->willReturn($attributeId);
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, ProductInterface::STATUS)
            ->willReturn($statusAttribute);

        $this->storeResolver->expects($this->once())
            ->method('getCurrentStoreId')
            ->willReturn($currentStoreId);

        $this->select->expects($this->at(0))
            ->method('joinLeft')
            ->with(
                ['status_global_attr' => $backendTable],
                "status_global_attr.entity_id = "
                . BaseSelectProcessorInterface::PRODUCT_RELATION_ALIAS . ".child_id"
                . " AND status_global_attr.attribute_id = {$attributeId}"
                . ' AND status_global_attr.store_id = ' . Store::DEFAULT_STORE_ID,
                []
            )
            ->willReturnSelf();
        $this->select->expects($this->at(1))
            ->method('joinLeft')
            ->with(
                ['status_attr' => $backendTable],
                "status_attr.entity_id = " . BaseSelectProcessorInterface::PRODUCT_RELATION_ALIAS . ".child_id"
                . " AND status_attr.attribute_id = {$attributeId}"
                . " AND status_attr.store_id = {$currentStoreId}",
                []
            )
            ->willReturnSelf();
        $this->select->expects($this->at(2))
            ->method('where')
            ->with('IFNULL(status_attr.value, status_global_attr.value) = ?', Status::STATUS_ENABLED)
            ->willReturnSelf();

        $this->assertEquals($this->select, $this->statusBaseSelectProcessor->process($this->select));
    }
}
