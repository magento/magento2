<?php
/**
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
 * @category    Mage
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Downloadable Products Observer
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_Model_Observer
{
    const XML_PATH_DISABLE_GUEST_CHECKOUT   = 'catalog/downloadable/disable_guest_checkout';

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_helper;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->_helper = isset($data['helper']) ? $data['helper'] : Mage::helper('Mage_Core_Helper_Data');
    }

    /**
     * Prepare product to save
     *
     * @param   Varien_Object $observer
     * @return  Mage_Downloadable_Model_Observer
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
     * @param Varien_Object $observer
     * @return Mage_Downloadable_Model_Observer
     */
    public function saveDownloadableOrderItem($observer)
    {
        $orderItem = $observer->getEvent()->getItem();
        if (!$orderItem->getId()) {
            //order not saved in the database
            return $this;
        }
        $product = $orderItem->getProduct();
        if ($product && $product->getTypeId() != Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            return $this;
        }
        $purchasedLink = Mage::getModel('Mage_Downloadable_Model_Link_Purchased')
            ->load($orderItem->getId(), 'order_item_id');
        if ($purchasedLink->getId()) {
            return $this;
        }
        if (!$product) {
            $product = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStoreId($orderItem->getOrder()->getStoreId())
                ->load($orderItem->getProductId());
        }
        if ($product->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            $links = $product->getTypeInstance()->getLinks($product);
            if ($linkIds = $orderItem->getProductOptionByCode('links')) {
                $linkPurchased = Mage::getModel('Mage_Downloadable_Model_Link_Purchased');
                Mage::helper('Mage_Core_Helper_Data')->copyFieldset(
                    'downloadable_sales_copy_order',
                    'to_downloadable',
                    $orderItem->getOrder(),
                    $linkPurchased
                );
                Mage::helper('Mage_Core_Helper_Data')->copyFieldset(
                    'downloadable_sales_copy_order_item',
                    'to_downloadable',
                    $orderItem,
                    $linkPurchased
                );
                $linkSectionTitle = (
                    $product->getLinksTitle()?
                    $product->getLinksTitle():Mage::getStoreConfig(Mage_Downloadable_Model_Link::XML_PATH_LINKS_TITLE)
                );
                $linkPurchased->setLinkSectionTitle($linkSectionTitle)
                    ->save();
                foreach ($linkIds as $linkId) {
                    if (isset($links[$linkId])) {
                        $linkPurchasedItem = Mage::getModel('Mage_Downloadable_Model_Link_Purchased_Item')
                            ->setPurchasedId($linkPurchased->getId())
                            ->setOrderItemId($orderItem->getId());

                        Mage::helper('Mage_Core_Helper_Data')->copyFieldset(
                            'downloadable_sales_copy_link',
                            'to_purchased',
                            $links[$linkId],
                            $linkPurchasedItem
                        );
                        $linkHash = strtr(base64_encode(microtime() . $linkPurchased->getId() . $orderItem->getId()
                            . $product->getId()), '+/=', '-_,');
                        $numberOfDownloads = $links[$linkId]->getNumberOfDownloads()*$orderItem->getQtyOrdered();
                        $linkPurchasedItem->setLinkHash($linkHash)
                            ->setNumberOfDownloadsBought($numberOfDownloads)
                            ->setStatus(Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING)
                            ->setCreatedAt($orderItem->getCreatedAt())
                            ->setUpdatedAt($orderItem->getUpdatedAt())
                            ->save();
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Set checkout session flag if order has downloadable product(s)
     *
     * @param Varien_Object $observer
     * @return Mage_Downloadable_Model_Observer
     */
    public function setHasDownloadableProducts($observer)
    {
        $session = Mage::getSingleton('Mage_Checkout_Model_Session');
        if (!$session->getHasDownloadableProducts()) {
            $order = $observer->getEvent()->getOrder();
            foreach ($order->getAllItems() as $item) {
                /* @var $item Mage_Sales_Model_Order_Item */
                if ($item->getProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
                || $item->getRealProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
                || $item->getProductOptionByCode('is_downloadable'))
                {
                    $session->setHasDownloadableProducts(true);
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Set status of link
     *
     * @param Varien_Object $observer
     * @return Mage_Downloadable_Model_Observer
     */
    public function setLinkStatus($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        /* @var $order Mage_Sales_Model_Order */
        $status = '';
        $linkStatuses = array(
            'pending'         => Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING,
            'expired'         => Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED,
            'avail'           => Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_AVAILABLE,
            'payment_pending' => Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING_PAYMENT,
            'payment_review'  => Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW
        );

        $downloadableItemsStatuses = array();
        $orderItemStatusToEnable = Mage::getStoreConfig(
            Mage_Downloadable_Model_Link_Purchased_Item::XML_PATH_ORDER_ITEM_STATUS, $order->getStoreId()
        );

        if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
            $status = $linkStatuses['pending'];
        } elseif ($order->isCanceled()
                  || $order->getState() == Mage_Sales_Model_Order::STATE_CLOSED
                  || $order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE
        ) {
            $expiredStatuses = array(
                Mage_Sales_Model_Order_Item::STATUS_CANCELED,
                Mage_Sales_Model_Order_Item::STATUS_REFUNDED,
            );
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
                    || $item->getRealProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
                ) {
                    if (in_array($item->getStatusId(), $expiredStatuses)) {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['expired'];
                    } else {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['avail'];
                    }
                }
            }
        } elseif ($order->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            $status = $linkStatuses['payment_pending'];
        } elseif ($order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
            $status = $linkStatuses['payment_review'];
        } else {
            $availableStatuses = array($orderItemStatusToEnable, Mage_Sales_Model_Order_Item::STATUS_INVOICED);
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
                    || $item->getRealProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
                ) {
                    if ($item->getStatusId() == Mage_Sales_Model_Order_Item::STATUS_BACKORDERED &&
                        $orderItemStatusToEnable == Mage_Sales_Model_Order_Item::STATUS_PENDING &&
                        !in_array(Mage_Sales_Model_Order_Item::STATUS_BACKORDERED, $availableStatuses, true) ) {
                        $availableStatuses[] = Mage_Sales_Model_Order_Item::STATUS_BACKORDERED;
                    }

                    if (in_array($item->getStatusId(), $availableStatuses)) {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['avail'];
                    }
                }
            }
        }
        if (!$downloadableItemsStatuses && $status) {
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
                    || $item->getRealProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
                ) {
                    $downloadableItemsStatuses[$item->getId()] = $status;
                }
            }
        }

        if ($downloadableItemsStatuses) {
            $linkPurchased = Mage::getResourceModel('Mage_Downloadable_Model_Resource_Link_Purchased_Item_Collection')
            ->addFieldToFilter('order_item_id', array('in' => array_keys($downloadableItemsStatuses)));
            foreach ($linkPurchased as $link) {
                if ($link->getStatus() != $linkStatuses['expired']
                    && !empty($downloadableItemsStatuses[$link->getOrderItemId()])
                ) {
                    $link->setStatus($downloadableItemsStatuses[$link->getOrderItemId()])
                    ->save();
                }
            }
        }
        return $this;
    }

    /**
     * Check is allowed guest checkout if quote contain downloadable product(s)
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Downloadable_Model_Observer
     */
    public function isAllowedGuestCheckout(Varien_Event_Observer $observer)
    {
        $quote  = $observer->getEvent()->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        $store  = $observer->getEvent()->getStore();
        $result = $observer->getEvent()->getResult();

        $isContain = false;

        foreach ($quote->getAllItems() as $item) {
            if (($product = $item->getProduct()) &&
            $product->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
                $isContain = true;
            }
        }

        if ($isContain && Mage::getStoreConfigFlag(self::XML_PATH_DISABLE_GUEST_CHECKOUT, $store)) {
            $result->setIsAllowed(false);
        }

        return $this;
    }

    /**
     * Initialize product options renderer with downloadable specific params
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Downloadable_Model_Observer
     */
    public function initOptionRenderer(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        $block->addOptionsRenderCfg('downloadable', 'Mage_Downloadable_Helper_Catalog_Product_Configuration');
        return $this;
    }

    /**
     * Duplicating downloadable product data
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Downloadable_Model_Observer
     */
    public function duplicateProduct($observer)
    {
        $currentProduct = $observer->getCurrentProduct();
        $newProduct = $observer->getNewProduct();
        if ($currentProduct->getTypeId() !== Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            //do nothing if not downloadable
            return $this;
        }
        $downloadableData = array();
        $type = $currentProduct->getTypeInstance();
        foreach ($type->getLinks($currentProduct) as $link) {
            $linkData = $link->getData();
            $downloadableData['link'][] = array(
                'is_delete'           => false,
                'link_id'             => null,
                'title'               => $linkData['title'],
                'is_shareable'        => $linkData['is_shareable'],
                'sample'              => array(
                    'type'       => $linkData['sample_type'],
                    'url'        => $linkData['sample_url'],
                    'file'       => $this->_helper->jsonEncode(array(array(
                        'file'   => $linkData['sample_file'],
                        'name'   => $linkData['sample_file'],
                        'size'   => 0,
                        'status' => null,
                    )))
                ),
                'file'       => $this->_helper->jsonEncode(array(array(
                    'file'   => $linkData['link_file'],
                    'name'   => $linkData['link_file'],
                    'size'   => 0,
                    'status' => null,
                ))),
                'type'                => $linkData['link_type'],
                'link_url'            => $linkData['link_url'],
                'sort_order'          => $linkData['sort_order'],
                'number_of_downloads' => $linkData['number_of_downloads'],
                'price'               => $linkData['price'],
            );
        }
        foreach ($type->getSamples($currentProduct) as $sample) {
            $sampleData = $sample->getData();
            $downloadableData['sample'][] = array(
                'is_delete'  => false,
                'sample_id'  => null,
                'title'      => $sampleData['title'],
                'type'       => $sampleData['sample_type'],
                'file'       => $this->_helper->jsonEncode(array(array(
                    'file'   => $sampleData['sample_file'],
                    'name'   => $sampleData['sample_file'],
                    'size'   => 0,
                    'status' => null,
                ))),
                'sample_url' => $sampleData['sample_url'],
                'sort_order' => $sampleData['sort_order'],
            );
        }
        $newProduct->setDownloadableData($downloadableData);
        return $this;
    }
}
