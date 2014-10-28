<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super;

use Magento\Catalog\Model\Resource\Eav\Attribute;
use Magento\TestFramework\ObjectManager;

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
        $this->assertEquals(array(), $block->getSelectedAttributes());
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
        $this->assertEquals(array($usedAttribute->getId()), array_keys($selectedAttributes));
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
        $productData = array(
            $usedAttribute->getId() => array(
                'label'    => static::ATTRIBUTE_LABEL,
                'position' => static::ATTRIBUTE_POSITION,
            ),

        );
        $this->getRequest()->setParam('product', array('configurable_attributes_data' => $productData));
        $attributes = $block->getAttributes();
        $this->assertArrayHasKey($usedAttribute->getId(), $attributes);

        $this->assertArrayHasKey('label', $attributes[$usedAttribute->getId()]);
        $this->assertEquals(static::ATTRIBUTE_LABEL, $attributes[$usedAttribute->getId()]['label']);

        $this->assertArrayHasKey('position', $attributes[$usedAttribute->getId()]);
        $this->assertEquals(static::ATTRIBUTE_POSITION, $attributes[$usedAttribute->getId()]['position']);
    }
}
