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

class StatusBaseSelectProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

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
        $this->select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();

        $this->statusBaseSelectProcessor =  (new ObjectManager($this))->getObject(StatusBaseSelectProcessor::class, [
            'eavConfig' => $this->eavConfig,
        ]);
    }

    public function testProcess()
    {
        $backendTable = 'backend_table';
        $attributeId = 'attribute_id';

        $statusAttribute = $this->getMockBuilder(AttributeInterface::class)
            ->setMethods(['getBackendTable', 'getAttributeId'])
            ->getMock();
        $statusAttribute->expects($this->once())
            ->method('getBackendTable')
            ->willReturn($backendTable);
        $statusAttribute->expects($this->once())
            ->method('getAttributeId')
            ->willReturn($attributeId);
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, ProductInterface::STATUS)
            ->willReturn($statusAttribute);

        $this->select->expects($this->once())
            ->method('join')
            ->with(
                ['status_attr' => $backendTable],
                'status_attr.entity_id = ' . BaseSelectProcessorInterface::PRODUCT_RELATION_ALIAS . '.entity_id',
                []
            )
            ->willReturnSelf();
        $this->select->expects($this->at(1))
            ->method('where')
            ->with('status_attr.attribute_id = ?', $attributeId)
            ->willReturnSelf();
        $this->select->expects($this->at(2))
            ->method('where')
            ->with('status_attr.value = ?', Status::STATUS_ENABLED)
            ->willReturnSelf();

        $this->assertEquals($this->select, $this->statusBaseSelectProcessor->process($this->select));
    }
}
