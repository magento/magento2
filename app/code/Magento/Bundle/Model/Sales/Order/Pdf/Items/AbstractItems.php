<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Sales Order Pdf Items renderer
 */
abstract class AbstractItems extends \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
{
    /**
     * Serializer
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem ,
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        SerializerInterface $serializer = null
    ) {
        $this->serializer = $serializer;

        parent::__construct($context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Getting all available children for Invoice, Shipment or CreditMemo item
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     */
    public function getChildren($item)
    {
        $itemsArray = [];

        $items = null;
        if ($item instanceof \Magento\Sales\Model\Order\Invoice\Item) {
            $items = $item->getInvoice()->getAllItems();
        } elseif ($item instanceof \Magento\Sales\Model\Order\Shipment\Item) {
            $items = $item->getShipment()->getAllItems();
        } elseif ($item instanceof \Magento\Sales\Model\Order\Creditmemo\Item) {
            $items = $item->getCreditmemo()->getAllItems();
        }

        if ($items) {
            foreach ($items as $value) {
                $parentItem = $value->getOrderItem()->getParentItem();
                if ($parentItem) {
                    $itemsArray[$parentItem->getId()][$value->getOrderItemId()] = $value;
                } else {
                    $itemsArray[$value->getOrderItem()->getId()][$value->getOrderItemId()] = $value;
                }
            }
        }

        if (isset($itemsArray[$item->getOrderItem()->getId()])) {
            return $itemsArray[$item->getOrderItem()->getId()];
        } else {
            return null;
        }
    }

    /**
     * Retrieve is Shipment Separately flag for Item
     *
     * @param \Magento\Framework\DataObject $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isShipmentSeparately($item = null)
    {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }
            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['shipment_type'])
                        && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY);
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    return !(isset($options['shipment_type'])
                        && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY);
                }
            }
        }

        $options = $this->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['shipment_type']) && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve is Child Calculated
     *
     * @param \Magento\Framework\DataObject $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isChildCalculated($item = null)
    {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }
            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['product_calculations'])
                        && $options['product_calculations'] == AbstractType::CALCULATE_CHILD);
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    return !(isset($options['product_calculations'])
                        && $options['product_calculations'] == AbstractType::CALCULATE_CHILD);
                }
            }
        }

        $options = $this->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['product_calculations'])
                && $options['product_calculations'] == AbstractType::CALCULATE_CHILD
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve Bundle Options
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getBundleOptions($item = null)
    {
        $options = $this->getOrderItem()->getProductOptions();
        if ($options && isset($options['bundle_options'])) {
            return $options['bundle_options'];
        }
        return [];
    }

    /**
     * Retrieve Selection attributes
     *
     * @param \Magento\Framework\DataObject $item
     * @return mixed
     */
    public function getSelectionAttributes($item)
    {
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }
        if (isset($options['bundle_selection_attributes'])) {
            return $this->getSerializer()->unserialize($options['bundle_selection_attributes']);
        }
        return null;
    }

    /**
     * Retrieve Order options
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getOrderOptions($item = null)
    {
        $result = [];
        $options = $this->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }

    /**
     * Retrieve Order Item
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getOrderItem()
    {
        if ($this->getItem() instanceof \Magento\Sales\Model\Order\Item) {
            return $this->getItem();
        } else {
            return $this->getItem()->getOrderItem();
        }
    }

    /**
     * Retrieve Value HTML
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getValueHtml($item)
    {
        $result = $this->filterManager->stripTags($item->getName());
        if (!$this->isShipmentSeparately($item)) {
            $attributes = $this->getSelectionAttributes($item);
            if ($attributes) {
                $result = $this->filterManager->sprintf($attributes['qty'], ['format' => '%d']) . ' x ' . $result;
            }
        }
        if (!$this->isChildCalculated($item)) {
            $attributes = $this->getSelectionAttributes($item);
            if ($attributes) {
                $result .= " " . $this->filterManager->stripTags(
                    $this->getOrderItem()->getOrder()->formatPrice($attributes['price'])
                );
            }
        }
        return $result;
    }

    /**
     * Can show price info for item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function canShowPriceInfo($item)
    {
        if ($item->getOrderItem()->getParentItem() && $this->isChildCalculated() ||
            !$item->getOrderItem()->getParentItem() && !$this->isChildCalculated()
        ) {
            return true;
        }
        return false;
    }

    /**
     * The getter function to get serializer
     *
     * @return SerializerInterface
     *
     * @deprecated
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()->get(SerializerInterface::class);
        }
        return $this->serializer;
    }
}
