<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super;

use Magento\Catalog\Model\Resource\Eav\Attribute;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \Magento\Backend\Utility\Controller
{
    const ATTRIBUTE_LABEL = 'New Attribute Label';
    const ATTRIBUTE_POSITION = 42;

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetSelectedAttributesForSimpleProductType()
    {
        $this->_objectManager->get(
            'Magento\Framework\Registry'
        )->register(
            'current_product',
            $this->_objectManager->create('Magento\Catalog\Model\Product')
        );

        /** @var $block \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config */
        $block = $this->_objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config'
        );
        $this->assertEquals([], $block->getSelectedAttributes());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetSelectedAttributesForConfigurableProductType()
    {
        $this->_objectManager->get(
            'Magento\Framework\Registry'
        )->register(
            'current_product',
            $this->_objectManager->create('Magento\Catalog\Model\Product')->load(1)
        );
        $this->_objectManager->get('Magento\Framework\View\LayoutInterface')
            ->createBlock('Magento\Framework\View\Element\Text', 'head');
        /** @var \Magento\Catalog\Model\Entity\Attribute $usedAttribute */
        $usedAttribute = $this->_objectManager->get(
            'Magento\Catalog\Model\Entity\Attribute'
        )->loadByCode(
            $this->_objectManager->get(
                'Magento\Eav\Model\Config'
            )->getEntityType(
                    'catalog_product'
                )->getId(),
            'test_configurable'
        );
        /** @var $block \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config */
        $block = $this->_objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config'
        );
        /** @var Attribute[] $selectedAttributes */
        $selectedAttributes = $block->getSelectedAttributes();
        $this->assertEquals([$usedAttribute->getId()], array_keys($selectedAttributes));
        /** @var Attribute $selectedAttribute */
        $selectedAttribute = reset($selectedAttributes);
        $this->assertEquals('test_configurable', $selectedAttribute->getAttributeCode());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testAttributesMergingByGetAttributesMethod()
    {
        $this->_objectManager->get(
            'Magento\Framework\Registry'
        )->register(
            'current_product',
            $this->_objectManager->create('Magento\Catalog\Model\Product')->load(1)
        );
        $this->_objectManager->get('Magento\Framework\View\LayoutInterface')
            ->createBlock('Magento\Framework\View\Element\Text', 'head');
        /** @var \Magento\Catalog\Model\Entity\Attribute $usedAttribute */
        $usedAttribute = $this->_objectManager->get(
            'Magento\Catalog\Model\Entity\Attribute'
        )->loadByCode(
            $this->_objectManager->get(
                'Magento\Eav\Model\Config'
            )->getEntityType(
                    'catalog_product'
                )->getId(),
            'test_configurable'
        );
        /** @var $block \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config */
        $block = $this->_objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config'
        );
        $productData = [
            $usedAttribute->getId() => [
                'label'    => static::ATTRIBUTE_LABEL,
                'position' => static::ATTRIBUTE_POSITION,
            ],

        ];
        $this->getRequest()->setParam('product', ['configurable_attributes_data' => $productData]);
        $attributes = $block->getAttributes();
        $this->assertArrayHasKey($usedAttribute->getId(), $attributes);

        $this->assertArrayHasKey('label', $attributes[$usedAttribute->getId()]);
        $this->assertEquals(static::ATTRIBUTE_LABEL, $attributes[$usedAttribute->getId()]['label']);

        $this->assertArrayHasKey('position', $attributes[$usedAttribute->getId()]);
        $this->assertEquals(static::ATTRIBUTE_POSITION, $attributes[$usedAttribute->getId()]['position']);
    }
}
