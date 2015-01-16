<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Store\Model\ScopeInterface;

/**
 * Downloadable Products Observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Observer
{
    const XML_PATH_DISABLE_GUEST_CHECKOUT = 'catalog/downloadable/disable_guest_checkout';

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_helper;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $_purchasedFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Downloadable\Model\Link\Purchased\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Downloadable\Model\Resource\Link\Purchased\Item\CollectionFactory
     */
    protected $_itemsFactory;

    /**
     * @var \Magento\Framework\Object\Copy
     */
    protected $_objectCopyService;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Downloadable\Model\Link\Purchased\ItemFactory $itemFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Downloadable\Model\Resource\Link\Purchased\Item\CollectionFactory $itemsFactory
     * @param \Magento\Framework\Object\Copy $objectCopyService
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Downloadable\Model\Link\Purchased\ItemFactory $itemFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Downloadable\Model\Resource\Link\Purchased\Item\CollectionFactory $itemsFactory,
        \Magento\Framework\Object\Copy $objectCopyService
    ) {
        $this->_helper = $coreData;
        $this->_scopeConfig = $scopeConfig;
        $this->_purchasedFactory = $purchasedFactory;
        $this->_productFactory = $productFactory;
        $this->_itemFactory = $itemFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_itemsFactory = $itemsFactory;
        $this->_objectCopyService = $objectCopyService;
    }

    /**
     * Prepare product to save
     *
     * @param   \Magento\Framework\Object $observer
     * @return  $this
     */
    public function prepareProductSave($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $product = $observer->getEvent()->getProduct();

        if ($downloadable = $request->getPost('downloadable')) {
            $product->setDownloadableData($downloadable);
        }

        return $this;
    }

    /**
     * Save data from order to purchased links
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function saveDownloadableOrderItem($observer)
    {
        $orderItem = $observer->getEvent()->getItem();
        if (!$orderItem->getId()) {
            //order not saved in the database
            return $this;
        }
        $product = $orderItem->getProduct();
        if ($product && $product->getTypeId() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $this;
        }
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
                    'downloadable_sales_copy_order',
                    'to_downloadable',
                    $orderItem->getOrder(),
                    $linkPurchased
                );
                $this->_objectCopyService->copyFieldsetToTarget(
                    'downloadable_sales_copy_order_item',
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
                            'downloadable_sales_copy_link',
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
     * Set checkout session flag if order has downloadable product(s)
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function setHasDownloadableProducts($observer)
    {
        if (!$this->_checkoutSession->getHasDownloadableProducts()) {
            $order = $observer->getEvent()->getOrder();
            foreach ($order->getAllItems() as $item) {
                /* @var $item \Magento\Sales\Model\Order\Item */
                if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                    || $item->getRealProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                    || $item->getProductOptionByCode(
                        'is_downloadable'
                    )
                ) {
                    $this->_checkoutSession->setHasDownloadableProducts(true);
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Set status of link
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function setLinkStatus($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        /* @var $order \Magento\Sales\Model\Order */
        $status = '';
        $linkStatuses = [
            'pending' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING,
            'expired' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED,
            'avail' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_AVAILABLE,
            'payment_pending' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING_PAYMENT,
            'payment_review' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PAYMENT_REVIEW,
        ];

        $downloadableItemsStatuses = [];
        $orderItemStatusToEnable = $this->_scopeConfig->getValue(
            \Magento\Downloadable\Model\Link\Purchased\Item::XML_PATH_ORDER_ITEM_STATUS,
            ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );

        if ($order->getState() == \Magento\Sales\Model\Order::STATE_HOLDED) {
            $status = $linkStatuses['pending'];
        } elseif ($order->isCanceled()
            || $order->getState() == \Magento\Sales\Model\Order::STATE_CLOSED
            || $order->getState() == \Magento\Sales\Model\Order::STATE_COMPLETE
        ) {
            $expiredStatuses = [
                \Magento\Sales\Model\Order\Item::STATUS_CANCELED,
                \Magento\Sales\Model\Order\Item::STATUS_REFUNDED,
            ];
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                    || $item->getRealProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    if (in_array($item->getStatusId(), $expiredStatuses)) {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['expired'];
                    } else {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['avail'];
                    }
                }
            }
        } elseif ($order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
            $status = $linkStatuses['payment_pending'];
        } elseif ($order->getState() == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW) {
            $status = $linkStatuses['payment_review'];
        } else {
            $availableStatuses = [$orderItemStatusToEnable, \Magento\Sales\Model\Order\Item::STATUS_INVOICED];
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                    || $item->getRealProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    if ($item->getStatusId() == \Magento\Sales\Model\Order\Item::STATUS_BACKORDERED
                        && $orderItemStatusToEnable == \Magento\Sales\Model\Order\Item::STATUS_PENDING
                        && !in_array(
                            \Magento\Sales\Model\Order\Item::STATUS_BACKORDERED,
                            $availableStatuses,
                            true
                        )
                    ) {
                        $availableStatuses[] = \Magento\Sales\Model\Order\Item::STATUS_BACKORDERED;
                    }

                    if (in_array($item->getStatusId(), $availableStatuses)) {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['avail'];
                    }
                }
            }
        }
        if (!$downloadableItemsStatuses && $status) {
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                    || $item->getRealProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    $downloadableItemsStatuses[$item->getId()] = $status;
                }
            }
        }

        if ($downloadableItemsStatuses) {
            $linkPurchased = $this->_createItemsCollection()->addFieldToFilter(
                'order_item_id',
                ['in' => array_keys($downloadableItemsStatuses)]
            );
            foreach ($linkPurchased as $link) {
                if ($link->getStatus() != $linkStatuses['expired']
                    && !empty($downloadableItemsStatuses[$link->getOrderItemId()])
                ) {
                    $link->setStatus($downloadableItemsStatuses[$link->getOrderItemId()])->save();
                }
            }
        }
        return $this;
    }

    /**
     * Check is allowed guest checkout if quote contain downloadable product(s)
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function isAllowedGuestCheckout(\Magento\Framework\Event\Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        $result = $observer->getEvent()->getResult();

        $result->setIsAllowed(true);

        if (!$this->_scopeConfig->isSetFlag(
            self::XML_PATH_DISABLE_GUEST_CHECKOUT,
            ScopeInterface::SCOPE_STORE,
            $store
        )) {
            return $this;
        }

        /* @var $quote \Magento\Sales\Model\Quote */
        $quote = $observer->getEvent()->getQuote();

        foreach ($quote->getAllItems() as $item) {
            if (($product = $item->getProduct())
                && $product->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
            ) {
                $result->setIsAllowed(false);
                break;
            }
        }

        return $this;
    }

    /**
     * Initialize product options renderer with downloadable specific params
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function initOptionRenderer(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        $block->addOptionsRenderCfg('downloadable', 'Magento\Downloadable\Helper\Catalog\Product\Configuration');
        return $this;
    }

    /**
     * @return \Magento\Downloadable\Model\Link\Purchased
     */
    protected function _createPurchasedModel()
    {
        return $this->_purchasedFactory->create();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function _createProductModel()
    {
        return $this->_productFactory->create();
    }

    /**
     * @return \Magento\Downloadable\Model\Link\Purchased\Item
     */
    protected function _createPurchasedItemModel()
    {
        return $this->_itemFactory->create();
    }

    /**
     * @return \Magento\Downloadable\Model\Resource\Link\Purchased\Item\Collection
     */
    protected function _createItemsCollection()
    {
        return $this->_itemsFactory->create();
    }
}
