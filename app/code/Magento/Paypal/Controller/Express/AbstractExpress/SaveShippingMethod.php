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
namespace Magento\Paypal\Controller\Express\AbstractExpress;

class SaveShippingMethod extends \Magento\Paypal\Controller\Express\AbstractExpress
{
    /**
     * Update shipping method (combined action for ajax and regular request)
     *
     * @return void
     */
    public function execute()
    {
        try {
            $isAjax = $this->getRequest()->getParam('isAjax');
            $this->_initCheckout();
            $this->_checkout->updateShippingMethod($this->getRequest()->getParam('shipping_method'));
            if ($isAjax) {
                $this->_view->loadLayout('paypal_express_review_details', true, true, false);
                $this->getResponse()->setBody(
                    $this->_view->getLayout()->getBlock('page.block')->setQuote($this->_getQuote())->toHtml()
                );
                return;
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t update shipping method.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        if ($isAjax) {
            $this->getResponse()->setBody(
                '<script type="text/javascript">window.location.href = '
                . $this->_url->getUrl('*/*/review')
                . ';</script>'
            );
        } else {
            $this->_redirect('*/*/review');
        }
    }
}
