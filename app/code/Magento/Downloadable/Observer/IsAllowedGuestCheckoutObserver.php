<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class IsAllowedGuestCheckoutObserver implements ObserverInterface
{
    /**
     *  Xml path to disable checkout
     */
    const XML_PATH_DISABLE_GUEST_CHECKOUT = 'catalog/downloadable/disable_guest_checkout';

    /**
     *  Xml path to get downloadable Shareable setting
     */
    const XML_PATH_DOWNLOADABLE_SHAREABLE = 'catalog/downloadable/shareable';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Downloadable link collection factory
     *
     * @var \Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory
     */
    protected $_linksFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory $linksFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_linksFactory = $linksFactory;
    }

    /**
     * Check is allowed guest checkout if quote contain downloadable product(s)
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        $result = $observer->getEvent()->getResult();

        /* @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getEvent()->getQuote();

        foreach ($quote->getAllItems() as $item) {
            if (($product = $item->getProduct())
                && $product->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
            ) {
                if ($this->_scopeConfig->isSetFlag(
                    self::XML_PATH_DISABLE_GUEST_CHECKOUT,
                    ScopeInterface::SCOPE_STORE,
                    $store
                ) || !$this->checkForShareableLinks($item)) {
                    $result->setIsAllowed(false);
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Check for shareable link
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return boolean
     */
    private function checkForShareableLinks($item)
    {
        $isSharable = true;
        $option = $item->getOptionByCode('downloadable_link_ids');
        if (!empty($option)) {
            $downloadableLinkIds = explode(',', $option->getValue());
            $links = $this->linksFactory->create()->addFieldToFilter("link_id", ["in" => $downloadableLinkIds]);
            foreach ($links as $link) {
                if (!$link->getIsShareable() ||
                    ($link->getIsShareable() == 2 && !$this->_scopeConfig->isSetFlag(
                        self::XML_PATH_DOWNLOADABLE_SHAREABLE,
                        ScopeInterface::SCOPE_STORE,
                        $store
                    )
                    )
                ) {
                    $isSharable = false;
                }
            }
        }
        return $isSharable;
    }
}
