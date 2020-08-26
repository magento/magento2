<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;
use Magento\Eav\Model\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Model\Plugin\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurableTest extends TestCase
{
    /** @var Config|MockObject */
    private $eavConfig;

    /** @var Data|MockObject */
    private $swatchHelper;

    /** @var Configurable|ObjectManager */
    protected $pluginModel;

    protected function setUp(): void
    {
        $this->eavConfig = $this->createPartialMock(
            Config::class,
            ['getEntityAttributeCodes', 'getAttribute']
        );
        $this->swatchHelper = $this->createPartialMock(
            Data::class,
            ['isVisualSwatch', 'isTextSwatch']
        );

        $objectManager = new ObjectManager($this);
        $this->pluginModel = $objectManager->getObject(
            Configurable::class,
            [
                'eavConfig' => $this->eavConfig,
                'swatchHelper' => $this->swatchHelper,
            ]
        );
    }

    public function testAfterGetUsedProductCollection()
    {
        $product = $this->getMockBuilder(ProductInterface::class)
            ->getMock();

        $subject = $this->createPartialMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
            ['getUsedProductAttributes']
        );
        $result = $this->createPartialMock(
            Collection::class,
            ['getEntity', 'addAttributeToSelect']
        );

        $attribute = $this->createMock(Attribute::class);

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
            Collection::class,
            $result
        );
    }
}
