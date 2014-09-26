<?php
/**
 *
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
namespace Magento\Checkout\Controller\Onepage;

class Index extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Checkout page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_objectManager->get('Magento\Checkout\Helper\Data')->canOnepageCheckout()) {
            $this->messageManager->addError(__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->_customerSession->regenerateId();
        $this->_objectManager->get('Magento\Checkout\Model\Session')->setCartWasUpdated(false);
        $currentUrl = $this->_objectManager->create('Magento\Framework\UrlInterface')
            ->getUrl(
                '*/*/*',
                array('_secure' => true)
            );
        $this->_objectManager->get('Magento\Customer\Model\Session')->setBeforeAuthUrl($currentUrl);
        $this->getOnepage()->initCheckout();
        $this->_view->loadLayout();
        $layout = $this->_view->getLayout();
        $layout->initMessages();
        $this->_view->getPage()->getConfig()->setTitle(__('Checkout'));
        $this->_view->renderLayout();
    }
}
