<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class ProductTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testSaveActionAssociatedProductIds()
    {
        $associatedProductIds = [3, 14, 15, 92];
        $this->getRequest()->setPost(
            [
                'attributes' => [$this->_getConfigurableAttribute()->getId()],
                'associated_product_ids' => $associatedProductIds,
            ]
        );

        $this->dispatch('backend/catalog/product/save');

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');
        $this->assertEquals($associatedProductIds, $product->getAssociatedProductIds());
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
