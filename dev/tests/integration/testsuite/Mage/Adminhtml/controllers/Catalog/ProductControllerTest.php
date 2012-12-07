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
 * @category    Magento
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Catalog_ProductControllerTest extends Mage_Adminhtml_Utility_Controller
{
    /**
     * @magentoDataFixture Mage/Catalog/_files/product_configurable.php
     */
    public function testSaveActionAssociatedProductIds()
    {
        $associatedProductIds = array(3, 14, 15, 92);
        $this->getRequest()->setPost(array(
            'attributes' => array($this->_getConfigurableAttribute()->getId()),
            'associated_product_ids' => $associatedProductIds,
        ));

        $this->dispatch('backend/admin/catalog_product/save');

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::registry('current_product');
        $this->assertEquals($associatedProductIds, $product->getAssociatedProductIds());
    }

    /**
     * Retrieve configurable attribute instance
     *
     * @return Mage_Catalog_Model_Entity_Attribute
     */
    protected function _getConfigurableAttribute()
    {
        return Mage::getModel('Mage_Catalog_Model_Entity_Attribute')->loadByCode(
            Mage::getSingleton('Mage_Eav_Model_Config')->getEntityType('catalog_product')->getId(),
            'test_configurable'
        );
    }

    public function testSaveActionWithDangerRequest()
    {
        $this->getRequest()->setPost(array(
            'product' => array(
                'entity_id' => 15
            ),
        ));
        $this->dispatch('backend/admin/catalog_product/save');
        /** @var Mage_Backend_Model_Session $session */
        $session = Mage::getSingleton('Mage_Backend_Model_Session');
        $errorMessages = $session->getMessages()->getErrors();
        $this->assertCount(1, $errorMessages);
        $this->assertEquals('Unable to save product', $errorMessages[0]->getCode());
        $this->assertRedirect($this->stringContains('/backend/admin/catalog_product/edit'));
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/product_configurable.php
     */
    public function testQuickCreateActionWithDangerRequest()
    {
        $this->getRequest()->setPost(array(
            'simple_product' => array(
                'entity_id' => 15
            ),
            'product' => 1
        ));
        $this->dispatch('backend/admin/catalog_product/quickcreate');
        $this->assertContains('"error":{"message":"Unable to create product","fields":{"sku":null}}',
            $this->getResponse()->getBody());
    }
}
