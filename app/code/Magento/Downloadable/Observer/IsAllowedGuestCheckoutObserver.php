<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Observer;

use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;

/**
 * Checks if guest checkout is allowed then quote contains downloadable products.
 */
class IsAllowedGuestCheckoutObserver implements ObserverInterface
{
    private const XML_PATH_DISABLE_GUEST_CHECKOUT = 'catalog/downloadable/disable_guest_checkout';

    private const XML_PATH_DOWNLOADABLE_SHAREABLE = 'catalog/downloadable/shareable';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Downloadable link collection factory
     *
     * @var CollectionFactory
     */
    private $linksFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $linksFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $linksFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->linksFactory = $linksFactory;
    }

    /**
     * Check is allowed guest checkout if quote contain downloadable product(s)
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        $result = $observer->getEvent()->getResult();

        /* @var $quote Quote */
        $quote = $observer->getEvent()->getQuote();

        foreach ($quote->getAllItems() as $item) {
            if (($product = $item->getProduct())
                && $product->getTypeId() == Type::TYPE_DOWNLOADABLE
            ) {
                if ($this->scopeConfig->isSetFlag(
                    self::XML_PATH_DISABLE_GUEST_CHECKOUT,
                    ScopeInterface::SCOPE_STORE,
                    $store
                )
                    || !$this->checkForShareableLinks($item, $store)) {
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
     * @param CartItemInterface $item
     * @param int $store
     * @return boolean
     */
    private function checkForShareableLinks(CartItemInterface $item, int $store): bool
    {
        $isSharable = true;
        $option = $item->getOptionByCode('downloadable_link_ids');
        if (!empty($option)) {
            $downloadableLinkIds = explode(',', $option->getValue());
            $links = $this->linksFactory->create()->addFieldToFilter("link_id", ["in" => $downloadableLinkIds]);
            foreach ($links as $link) {
                if (!$link->getIsShareable() ||
                    (
                        $link->getIsShareable() == 2 && !$this->scopeConfig->isSetFlag(
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
