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
        $associatedProductIds = array(3, 14, 15, 92);
        $this->getRequest()->setPost(
            array(
                'attributes' => array($this->_getConfigurableAttribute()->getId()),
                'associated_product_ids' => $associatedProductIds
            )
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
