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
namespace Magento\ProductAlert\Controller\Add;

use Magento\Framework\App\Action\Context;

class Price extends \Magento\ProductAlert\Controller\Add
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $customerSession);
    }

    /**
     * Check if URL is internal
     *
     * @param string $url
     * @return bool
     */
    protected function _isInternal($url)
    {
        if (strpos($url, 'http') === false) {
            return false;
        }
        $currentStore = $this->_storeManager->getStore();
        return strpos(
            $url,
            $currentStore->getBaseUrl()
        ) === 0 || strpos(
            $url,
            $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true)
        ) === 0;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $backUrl = $this->getRequest()->getParam(\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED);
        $productId = (int)$this->getRequest()->getParam('product_id');
        if (!$backUrl || !$productId) {
            $this->_redirect('/');
            return;
        }

        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        if (!$product->getId()) {
            /* @var $product \Magento\Catalog\Model\Product */
            $this->messageManager->addError(__('There are not enough parameters.'));
            if ($this->_isInternal($backUrl)) {
                $this->getResponse()->setRedirect($backUrl);
            } else {
                $this->_redirect('/');
            }
            return;
        }

        try {
            $model = $this->_objectManager->create(
                'Magento\ProductAlert\Model\Price'
            )->setCustomerId(
                $this->_customerSession->getCustomerId()
            )->setProductId(
                $product->getId()
            )->setPrice(
                $product->getFinalPrice()
            )->setWebsiteId(
                $this->_objectManager->get('Magento\Framework\StoreManagerInterface')->getStore()->getWebsiteId()
            );
            $model->save();
            $this->messageManager->addSuccess(__('You saved the alert subscription.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
