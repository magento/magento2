<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Observer;

use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory as LinkCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var LinkCollectionFactory
     */
    private $linkCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LinkCollectionFactory $linkCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LinkCollectionFactory $linkCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->linkCollectionFactory = $linkCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Check is allowed guest checkout if quote contain downloadable product(s)
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $storeId = (int)$this->storeManager->getStore($observer->getEvent()->getStore())->getId();
        $result = $observer->getEvent()->getResult();

        /* @var $quote Quote */
        $quote = $observer->getEvent()->getQuote();
        $isGuestCheckoutDisabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISABLE_GUEST_CHECKOUT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();

            if ((string)$product->getTypeId() === Type::TYPE_DOWNLOADABLE) {
                if ($isGuestCheckoutDisabled || !$this->checkForShareableLinks($item, $storeId)) {
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
     * @param int $storeId
     * @return boolean
     */
    private function checkForShareableLinks(CartItemInterface $item, int $storeId): bool
    {
        $isSharable = true;
        $option = $item->getOptionByCode('downloadable_link_ids');

        if (!empty($option)) {
            $downloadableLinkIds = explode(',', $option->getValue());

            $linkCollection = $this->linkCollectionFactory->create();
            $linkCollection->addFieldToFilter('link_id', ['in' => $downloadableLinkIds]);
            $linkCollection->addFieldToFilter('is_shareable', ['in' => $this->getNotSharableValues($storeId)]);

            // We don't have not sharable links
            $isSharable = $linkCollection->getSize() === 0;
        }

        return $isSharable;
    }

    /**
     * Returns not sharable values depending on configuration
     *
     * @param int $storeId
     * @return array
     */
    private function getNotSharableValues(int $storeId): array
    {
        $configIsSharable = $this->scopeConfig->isSetFlag(
            self::XML_PATH_DOWNLOADABLE_SHAREABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $notShareableValues = [Link::LINK_SHAREABLE_NO];

        if (!$configIsSharable) {
            $notShareableValues[] = Link::LINK_SHAREABLE_CONFIG;
        }

        return $notShareableValues;
    }
}
