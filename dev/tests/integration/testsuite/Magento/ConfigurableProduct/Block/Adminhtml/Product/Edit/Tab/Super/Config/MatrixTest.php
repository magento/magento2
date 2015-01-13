<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config;


/**
 * @magentoAppArea adminhtml
 */
class MatrixTest extends \Magento\Backend\Utility\Controller
{
    const ATTRIBUTE_LABEL = 'New Attribute Label';
    const ATTRIBUTE_POSITION = 42;

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetVariations()
    {
        $this->_objectManager->get(
            'Magento\Framework\Registry'
        )->register(
            'current_product',
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Catalog\Model\Product'
            )->load(1)
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Framework\View\Element\Text',
            'head'
        );
        /** @var $usedAttribute \Magento\Catalog\Model\Entity\Attribute */
        $usedAttribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Entity\Attribute'
        )->loadByCode(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Eav\Model\Config'
            )->getEntityType(
                'catalog_product'
            )->getId(),
            'test_configurable'
        );
        $attributeOptions = $usedAttribute->getSource()->getAllOptions(false);
        /** @var $block \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            preg_replace('/Test$/', '', __CLASS__)
        );

        $variations = $block->getVariations();
        foreach ($variations as &$variation) {
            foreach ($variation as &$row) {
                unset($row['price']);
            }
        }

        $this->assertEquals(
            [
                [$usedAttribute->getId() => $attributeOptions[0]],
                [$usedAttribute->getId() => $attributeOptions[1]],
            ],
            $variations
        );
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
            $this->_objectManager->get('Magento\Eav\Model\Config')
                ->getEntityType('catalog_product')->getId(),
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
