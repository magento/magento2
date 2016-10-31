<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * @magentoAppArea adminhtml
 */
class MatrixTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    const ATTRIBUTE_LABEL = 'New Attribute Label';
    const ATTRIBUTE_POSITION = 42;

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetVariations()
    {
        $productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('configurable');
        $this->_objectManager->get(
            'Magento\Framework\Registry'
        )->register(
            'current_product',
            $product
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
        /** @var $block \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Matrix */
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
}
