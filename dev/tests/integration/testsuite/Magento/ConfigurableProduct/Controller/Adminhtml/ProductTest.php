<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\TestFramework\ObjectManager;

/**
 * @magentoAppArea adminhtml
 */
class ProductTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testSaveActionAssociatedProductIds()
    {
        $associatedProductIds = [3, 14, 15, 92];
        $associatedProductIdsJSON = json_encode($associatedProductIds);
        $this->getRequest()->setPostValue(
            [
                'attributes' => [$this->_getConfigurableAttribute()->getId()],
                'product' => ['associated_product_ids_serialized' => $associatedProductIdsJSON]
            ]
        );

        $this->dispatch('backend/catalog/product/save');

        /** @var $objectManager ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $product Product */
        $product = $objectManager->get(Registry::class)->registry('current_product');

        self::assertEquals($associatedProductIds, $product->getExtensionAttributes()->getConfigurableProductLinks());
    }

    /**
     * Retrieve configurable attribute instance
     *
     * @return \Magento\Catalog\Model\Entity\Attribute
     */
    protected function _getConfigurableAttribute()
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Entity\Attribute'
        )->loadByCode(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Eav\Model\Config'
            )->getEntityType(
                'catalog_product'
            )->getId(),
            'test_configurable'
        );
    }
}
