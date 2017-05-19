<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
            \Magento\Eav\Model\Config::class,
            ['getEntityAttributeCodes', 'getAttribute'],
            [],
            '',
            false
        );
        $this->swatchHelper = $this->getMock(
            \Magento\Swatches\Helper\Data::class,
            ['isVisualSwatch', 'isTextSwatch'],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->pluginModel = $objectManager->getObject(
            \Magento\Swatches\Model\Plugin\Configurable::class,
            [
                'eavConfig' => $this->eavConfig,
                'swatchHelper' => $this->swatchHelper,
            ]
        );
    }

    public function testAfterGetUsedProductCollection()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)->getMock();

        $subject = $this->getMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
            ['getUsedProductAttributes'],
            [],
            '',
            false
        );
        $result = $this->getMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection::class,
            ['getEntity', 'addAttributeToSelect'],
            [],
            '',
            false
        );

        $attribute = $this->getMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, [], [], '', false);

        $subject->expects($this->once())->method('getUsedProductAttributes')->with($product)
            ->willReturn(['size' => $attribute, 'color' => $attribute, 'swatch1' => $attribute]);

        $attribute->expects($this->any())
            ->method('getData')
            ->with('additional_data')
            ->willReturn(true);
        $this->swatchHelper->expects($this->exactly(3))->method('isVisualSwatch')->with($attribute)->willReturn(true);

        $result->expects($this->once())->method('addAttributeToSelect')
            ->with($this->identicalTo(['image', 'size', 'color', 'swatch1']))->willReturn($result);

        $result = $this->pluginModel->afterGetUsedProductCollection($subject, $result, $product);
        $this->assertInstanceOf(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection::class,
            $result
        );
    }
}
