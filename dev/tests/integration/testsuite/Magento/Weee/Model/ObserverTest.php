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
namespace Magento\Weee\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Weee\Model\Observer
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Weee\Model\Observer'
        );
    }

    /**
     * @magentoConfigFixture current_store tax/weee/enable 1
     * @magentoDataFixture Magento/Weee/_files/product_with_fpt.php
     */
    public function testUpdateProductOptions()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->unregister('current_product');
        $eventObserver = $this->_createEventObserverForUpdateConfigurableProductOptions();
        $this->_model->updateProductOptions($eventObserver);
        $this->assertEquals(array(), $eventObserver->getEvent()->getResponseObject()->getAdditionalOptions());

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $objectManager->get('Magento\Framework\Registry')->register('current_product', $product->load(1));

        foreach (array(\Magento\Weee\Model\Tax::DISPLAY_INCL, \Magento\Weee\Model\Tax::DISPLAY_INCL_DESCR) as $mode) {
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\App\Config\MutableScopeConfigInterface'
            )->setValue(
                Config::XML_PATH_FPT_DISPLAY_PRODUCT_VIEW,
                $mode,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $eventObserver = $this->_createEventObserverForUpdateConfigurableProductOptions();
            $this->_model->updateProductOptions($eventObserver);
            $this->assertEquals(
                array('oldPlusDisposition' => 0.07, 'plusDisposition' => 0.07),
                $eventObserver->getEvent()->getResponseObject()->getAdditionalOptions()
            );
        }

        foreach (array(
            \Magento\Weee\Model\Tax::DISPLAY_EXCL,
            \Magento\Weee\Model\Tax::DISPLAY_EXCL_DESCR_INCL
        ) as $mode) {
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\App\Config\MutableScopeConfigInterface'
            )->setValue(
                Config::XML_PATH_FPT_DISPLAY_PRODUCT_VIEW,
                $mode,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $eventObserver = $this->_createEventObserverForUpdateConfigurableProductOptions();
            $this->_model->updateProductOptions($eventObserver);
            $this->assertEquals(
                array('oldPlusDisposition' => 0.07, 'plusDisposition' => 0.07, 'exclDisposition' => true),
                $eventObserver->getEvent()->getResponseObject()->getAdditionalOptions()
            );
        }
    }

    /**
     * @return \Magento\Framework\Event\Observer
     */
    protected function _createEventObserverForUpdateConfigurableProductOptions()
    {
        $response = new \Magento\Framework\Object(array('additional_options' => array()));
        $event = new \Magento\Framework\Event(array('response_object' => $response));
        return new \Magento\Framework\Event\Observer(array('event' => $event));
    }
}
