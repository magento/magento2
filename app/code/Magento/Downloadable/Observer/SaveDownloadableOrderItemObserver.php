<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class SaveDownloadableOrderItemObserver implements ObserverInterface
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     * @since 2.0.0
     */
    protected $_purchasedFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     * @since 2.0.0
     */
    protected $_productFactory;

    /**
     * @var \Magento\Downloadable\Model\Link\Purchased\ItemFactory
     * @since 2.0.0
     */
    protected $_itemFactory;

    /**
     * @var \Magento\Framework\DataObject\Copy
     * @since 2.0.0
     */
    protected $_objectCopyService;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     * @since 2.0.0
     */
    protected $_itemsFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Downloadable\Model\Link\Purchased\ItemFactory $itemFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Downloadable\Model\Link\Purchased\ItemFactory $itemFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_purchasedFactory = $purchasedFactory;
        $this->_productFactory = $productFactory;
        $this->_itemFactory = $itemFactory;
        $this->_itemsFactory = $itemsFactory;
        $this->_objectCopyService = $objectCopyService;
    }

    /**
     * Save data from order to purchased links
     *
     * @param \Magento\Framework\DataObject $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderItem = $observer->getEvent()->getItem();
        if (!$orderItem->getId()) {
            //order not saved in the database
            return $this;
        }
        if ($orderItem->getProductType() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $this;
        }
        $product = $orderItem->getProduct();
        $purchasedLink = $this->_createPurchasedModel()->load($orderItem->getId(), 'order_item_id');
        if ($purchasedLink->getId()) {
            return $this;
        }
        if (!$product) {
            $product = $this->_createProductModel()->setStoreId(
                $orderItem->getOrder()->getStoreId()
            )->load(
                $orderItem->getProductId()
            );
        }
        if ($product->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            $links = $product->getTypeInstance()->getLinks($product);
            if ($linkIds = $orderItem->getProductOptionByCode('links')) {
                $linkPurchased = $this->_createPurchasedModel();
                $this->_objectCopyService->copyFieldsetToTarget(
                    \downloadable_sales_copy_order::class,
                    'to_downloadable',
                    $orderItem->getOrder(),
                    $linkPurchased
                );
                $this->_objectCopyService->copyFieldsetToTarget(
                    \downloadable_sales_copy_order_item::class,
                    'to_downloadable',
                    $orderItem,
                    $linkPurchased
                );
                $linkSectionTitle = $product->getLinksTitle() ? $product
                    ->getLinksTitle() : $this
                    ->_scopeConfig
                    ->getValue(
                        \Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE,
                        ScopeInterface::SCOPE_STORE
                    );
                $linkPurchased->setLinkSectionTitle($linkSectionTitle)->save();
                foreach ($linkIds as $linkId) {
                    if (isset($links[$linkId])) {
                        $linkPurchasedItem = $this->_createPurchasedItemModel()->setPurchasedId(
                            $linkPurchased->getId()
                        )->setOrderItemId(
                            $orderItem->getId()
                        );

                        $this->_objectCopyService->copyFieldsetToTarget(
                            \downloadable_sales_copy_link::class,
                            'to_purchased',
                            $links[$linkId],
                            $linkPurchasedItem
                        );
                        $linkHash = strtr(
                            base64_encode(
                                microtime() . $linkPurchased->getId() . $orderItem->getId() . $product->getId()
                            ),
                            '+/=',
                            '-_,'
                        );
                        $numberOfDownloads = $links[$linkId]->getNumberOfDownloads() * $orderItem->getQtyOrdered();
                        $linkPurchasedItem->setLinkHash(
                            $linkHash
                        )->setNumberOfDownloadsBought(
                            $numberOfDownloads
                        )->setStatus(
                            \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING
                        )->setCreatedAt(
                            $orderItem->getCreatedAt()
                        )->setUpdatedAt(
                            $orderItem->getUpdatedAt()
                        )->save();
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return \Magento\Downloadable\Model\Link\Purchased
     * @since 2.0.0
     */
    protected function _createPurchasedModel()
    {
        return $this->_purchasedFactory->create();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    protected function _createProductModel()
    {
        return $this->_productFactory->create();
    }

    /**
     * @return \Magento\Downloadable\Model\Link\Purchased\Item
     * @since 2.0.0
     */
    protected function _createPurchasedItemModel()
    {
        return $this->_itemFactory->create();
    }

    /**
     * @return \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection
     * @since 2.0.0
     */
    protected function _createItemsCollection()
    {
        return $this->_itemsFactory->create();
    }
}
