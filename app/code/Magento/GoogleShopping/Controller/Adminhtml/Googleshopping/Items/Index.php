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
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items;

class Index extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items
{
    /**
     * Initialize general settings for action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_GoogleShopping::catalog_googleshopping_items'
        )->_addBreadcrumb(
            __('Catalog'),
            __('Catalog')
        )->_addBreadcrumb(
            __('Google Content'),
            __('Google Content')
        );
        return $this;
    }

    /**
     * Manage Items page with two item grids: Magento products and Google Content items
     *
     * @return void
     */
    public function execute()
    {
        $this->_title->add(__('Google Content Items'));

        if (0 === (int)$this->getRequest()->getParam('store')) {
            $this->_redirect(
                'adminhtml/*/',
                array(
                    'store' => $this->_objectManager->get(
                        'Magento\Framework\StoreManagerInterface'
                    )->getStore()->getId(),
                    '_current' => true
                )
            );
            return;
        }

        $this->_initAction();

        $contentBlock = $this->_view->getLayout()->createBlock(
            'Magento\GoogleShopping\Block\Adminhtml\Items'
        )->setStore(
            $this->_getStore()
        );

        if ($this->getRequest()->getParam('captcha_token') && $this->getRequest()->getParam('captcha_url')) {
            $contentBlock->setGcontentCaptchaToken(
                $this->_objectManager->get(
                    'Magento\Core\Helper\Data'
                )->urlDecode(
                    $this->getRequest()->getParam('captcha_token')
                )
            );
            $contentBlock->setGcontentCaptchaUrl(
                $this->_objectManager->get(
                    'Magento\Core\Helper\Data'
                )->urlDecode(
                    $this->getRequest()->getParam('captcha_url')
                )
            );
        }

        if (!$this->_objectManager->get(
            'Magento\GoogleShopping\Model\Config'
        )->isValidDefaultCurrencyCode(
            $this->_getStore()->getId()
        )
        ) {
            $_countryInfo = $this->_objectManager->get(
                'Magento\GoogleShopping\Model\Config'
            )->getTargetCountryInfo(
                $this->_getStore()->getId()
            );
            $this->messageManager->addNotice(
                __(
                    "The store's currency should be set to %1 for %2 in system configuration. Otherwise item prices won't be correct in Google Content.",
                    $_countryInfo['currency_name'],
                    $_countryInfo['name']
                )
            );
        }

        $this->_addBreadcrumb(__('Items'), __('Items'))->_addContent($contentBlock);
        $this->_view->renderLayout();
    }
}
