<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;

/**
 * Test add option of swatch attribute
 *
 */
class SwatchAttributeOptionAddTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Swatches/_files/swatch_attribute.php
     */
    public function testSwatchOptionAdd()
    {
        /** @var \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute */
        $attribute = $this->objectManager
            ->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->load('color_swatch', 'attribute_code');
        $optionsPerAttribute = 3;

        $data['options']['option'] = array_reduce(
            range(10, $optionsPerAttribute),
            function ($values, $index) use ($optionsPerAttribute) {
                $values[] = [
                    'label' => 'option ' . $index,
                    'value' => 'option_' . $index
                ];
                return $values;
            },
            []
        );

        /** @var AttributeOptionInterface[] $options */
        $options = [];
        foreach ($data['options']['option'] as $optionData) {
            $options[] = $this->objectManager
                ->get(AttributeOptionInterfaceFactory::class)
                ->create(['data' => $optionData]);
        }

        /** @var ProductAttributeOptionManagementInterface $optionManagement */
        $optionManagement = $this->objectManager->get(ProductAttributeOptionManagementInterface::class);
        foreach ($options as $option) {
            $optionManagement->add(
                $attribute->getAttributeCode(),
                $option
            );
        }

        $items = $optionManagement->getItems($attribute->getAttributeCode());
        array_walk(
            $items,
            function (&$item) {
                /** @var  AttributeOptionInterface $item */
                $item = $item->getLabel();
            }
        );
        foreach ($options as $option) {
            $this->assertTrue(in_array($option->getLabel(), $items));
        }
    }
}
