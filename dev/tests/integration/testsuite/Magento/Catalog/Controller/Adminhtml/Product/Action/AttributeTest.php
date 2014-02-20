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
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Catalog\Controller\Adminhtml\Product\Action;

/**
 * @magentoAppArea adminhtml
 */
class AttributeTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute::saveAction
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSaveActionRedirectsSuccessfully()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $session \Magento\Backend\Model\Session */
        $session = $objectManager->get('Magento\Backend\Model\Session');
        $session->setProductIds(array(1));

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        /** @var \Magento\Backend\Model\UrlInterface $urlBuilder */
        $urlBuilder = $objectManager->get('Magento\UrlInterface');

        /** @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper */
        $attributeHelper = $objectManager->get('Magento\Catalog\Helper\Product\Edit\Action\Attribute');
        $expectedUrl = $urlBuilder->getUrl(
            'catalog/product/index', array('store' => $attributeHelper->getSelectedStoreId()))
        ;
        $isRedirectPresent = false;
        foreach ($this->getResponse()->getHeaders() as $header) {
            if ($header['name'] === 'Location' && strpos($header['value'], $expectedUrl) === 0) {
                $isRedirectPresent = true;
            }
        }

        $this->assertTrue($isRedirectPresent);
    }
}
