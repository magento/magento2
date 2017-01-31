<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Model\Plugin;


class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    private $eavConfig;

    /** @var \Magento\Swatches\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    private $swatchHelper;

    /** @var \Magento\Swatches\Model\Plugin\Configurable|\Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $pluginModel;
    
    protected function setUp()
    {
        $this->eavConfig = $this->getMock(
            '\Magento\Eav\Model\Config',
            ['getEntityAttributeCodes', 'getAttribute'],
            [],
            '',
            false
        );
        $this->swatchHelper = $this->getMock(
            '\Magento\Swatches\Helper\Data',
            ['isVisualSwatch', 'isTextSwatch'],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->pluginModel = $objectManager->getObject(
            '\Magento\Swatches\Model\Plugin\Configurable',
            [
                'eavConfig' => $this->eavConfig,
                'swatchHelper' => $this->swatchHelper,
            ]
        );
    }

    public function testAfterGetUsedProductCollection()
    {
        $subject = $this->getMock('\Magento\ConfigurableProduct\Model\Product\Type\Configurable', [], [], '', false);
        $result = $this->getMock(
            '\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection',
            ['getEntity', 'addAttributeToSelect'],
            [],
            '',
            false
        );

        $collectionEntity = $this->getMock(
            '\Magento\Eav\Model\Entity\Collection\AbstractCollection',
            ['getType'],
            [],
            '',
            false
        );
        $collectionEntity->expects($this->once())->method('getType')->willReturn('catalog');
        $result->expects($this->once())->method('getEntity')->willReturn($collectionEntity);

        $attribute = $this->getMock('\Magento\Catalog\Model\ResourceModel\Eav\Attribute', [], [], '', false);

        $this->eavConfig->expects($this->once())->method('getEntityAttributeCodes')->with('catalog')
            ->willReturn(['size', 'color', 'swatch1']);

        $this->eavConfig->expects($this->exactly(3))->method('getAttribute')->willReturn($attribute);
        $attribute->expects($this->any())
            ->method('getData')
            ->with('additional_data')
            ->willReturn(true);
        $this->swatchHelper->expects($this->exactly(3))->method('isVisualSwatch')->with($attribute)->willReturn(true);

        $result->expects($this->once())->method('addAttributeToSelect')
            ->with($this->identicalTo(['image', 'size', 'color', 'swatch1']))->willReturn($result);

        $result = $this->pluginModel->afterGetUsedProductCollection($subject, $result);
        $this->assertInstanceOf(
            '\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection',
            $result
        );
    }
}
