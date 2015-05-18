<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items;

use Magento\Backend\App\Action;
use Magento\Framework\Notification\NotifierInterface;

class Index extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items
{
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @param Action\Context $context
     * @param NotifierInterface $notifier
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     */
    public function __construct(
        Action\Context $context,
        NotifierInterface $notifier,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        parent::__construct($context, $notifier, $urlEncoder);
        $this->urlDecoder = $urlDecoder;
    }

    /**
     * Manage Items page with two item grids: Magento products and Google Content items
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if (0 === (int)$this->getRequest()->getParam('store')) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath(
                'adminhtml/*/',
                [
                    'store' => $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                        ->getStore()->getId(),
                    '_current' => true
                ]
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_GoogleShopping::catalog_googleshopping_items')
            ->addBreadcrumb(__('Catalog'), __('Catalog'))
            ->addBreadcrumb(__('Google Content'), __('Google Content'));
        $resultPage->getConfig()->getTitle()->prepend(__('Google Content Items'));

        $contentBlock = $resultPage->getLayout()
            ->createBlock('Magento\GoogleShopping\Block\Adminhtml\Items')
            ->setStore($this->_getStore());

        if ($this->getRequest()->getParam('captcha_token') && $this->getRequest()->getParam('captcha_url')) {
            $contentBlock->setGcontentCaptchaToken(
                $this->urlDecoder->decode(
                    $this->getRequest()->getParam('captcha_token')
                )
            );
            $contentBlock->setGcontentCaptchaUrl(
                $this->urlDecoder->decode(
                    $this->getRequest()->getParam('captcha_url')
                )
            );
        }

        if (!$this->_objectManager->get('Magento\GoogleShopping\Model\Config')
                ->isValidDefaultCurrencyCode($this->_getStore()->getId())
        ) {
            $_countryInfo = $this->_objectManager->get(
                'Magento\GoogleShopping\Model\Config'
            )->getTargetCountryInfo(
                $this->_getStore()->getId()
            );
            $this->messageManager->addNotice(
                __(
                    "The store's currency should be set to %1 for %2 in system configuration."
                        . " Otherwise item prices won't be correct in Google Content.",
                    $_countryInfo['currency_name'],
                    $_countryInfo['name']
                )
            );
        }

        return $resultPage->addBreadcrumb(__('Items'), __('Items'))->addContent($contentBlock);
    }
}
