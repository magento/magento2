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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Service\V1;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Service\V1\Data\OrderTaxDetails;
use Magento\Tax\Service\V1\Data\OrderTaxDetailsBuilder;
use Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTax;
use Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTaxBuilder;
use Magento\Tax\Service\V1\Data\OrderTaxDetails\Item;

/**
 * Order tax service.
 */
class OrderTaxService implements OrderTaxServiceInterface
{
    /**
     * @var \Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory
     */
    protected $orderItemTaxFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderTaxDetailsBuilder
     */
    protected $orderTaxDetailsBuilder;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory $orderItemTaxFactory
     * @param OrderTaxDetailsBuilder $orderTaxDetailsBuilder
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory $orderItemTaxFactory,
        OrderTaxDetailsBuilder $orderTaxDetailsBuilder
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderItemTaxFactory = $orderItemTaxFactory;
        $this->orderTaxDetailsBuilder = $orderTaxDetailsBuilder;
    }

    /**
     * Convert applied tax from array to data object
     *
     * @param AppliedTaxBuilder $appliedTaxBuilder
     * @param array $itemAppliedTax
     * @return AppliedTax
     */
    protected function convertToAppliedTaxDataObject(AppliedTaxBuilder $appliedTaxBuilder, $itemAppliedTax)
    {
        $appliedTaxBuilder->setCode($itemAppliedTax['code']);
        $appliedTaxBuilder->setTitle($itemAppliedTax['title']);
        $appliedTaxBuilder->setPercent($itemAppliedTax['tax_percent']);
        $appliedTaxBuilder->setAmount($itemAppliedTax['real_amount']);
        $appliedTaxBuilder->setBaseAmount($itemAppliedTax['real_base_amount']);

        return $appliedTaxBuilder->create();
    }

    /**
     * Aggregate item applied taxes to get order applied taxes
     *
     * @param AppliedTaxBuilder $appliedTaxBuilder
     * @param Item[] $items
     * @return AppliedTax[]
     */
    protected function aggregateAppliedTaxes(AppliedTaxBuilder $appliedTaxBuilder, $items)
    {
        $orderAppliedTaxes = [];
        $orderAppliedTaxesData = [];
        foreach ($items as $item) {
            $itemAppliedTaxes = $item->getAppliedTaxes();
            foreach ($itemAppliedTaxes as $itemAppliedTax) {
                $code = $itemAppliedTax->getCode();
                if (!isset($orderAppliedTaxesData[$code])) {
                    $orderAppliedTaxesData[$code] = [
                        AppliedTax::KEY_CODE => $code,
                        AppliedTax::KEY_TITLE => $itemAppliedTax->getTitle(),
                        AppliedTax::KEY_PERCENT => $itemAppliedTax->getPercent(),
                        AppliedTax::KEY_AMOUNT => $itemAppliedTax->getAmount(),
                        AppliedTax::KEY_BASE_AMOUNT => $itemAppliedTax->getBaseAmount(),
                    ];
                } else {
                    $orderAppliedTaxesData[$code][AppliedTax::KEY_AMOUNT] += $itemAppliedTax->getAmount();
                    $orderAppliedTaxesData[$code][AppliedTax::KEY_BASE_AMOUNT] += $itemAppliedTax->getBaseAmount();
                }
            }
        }
        foreach ($orderAppliedTaxesData as $orderAppliedTaxData) {
            $appliedTaxBuilder->setCode($orderAppliedTaxData[AppliedTax::KEY_CODE]);
            $appliedTaxBuilder->setTitle($orderAppliedTaxData[AppliedTax::KEY_TITLE]);
            $appliedTaxBuilder->setPercent($orderAppliedTaxData[AppliedTax::KEY_PERCENT]);
            $appliedTaxBuilder->setAmount($orderAppliedTaxData[AppliedTax::KEY_AMOUNT]);
            $appliedTaxBuilder->setBaseAmount($orderAppliedTaxData[AppliedTax::KEY_BASE_AMOUNT]);
            $orderAppliedTaxes[] = $appliedTaxBuilder->create();
        }
        return $orderAppliedTaxes;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderTaxDetails($orderId)
    {
        $order = $this->orderFactory->create()->load($orderId);
        if (!$order) {
            throw new NoSuchEntityException(
                NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                [
                    'fieldName' => 'orderId',
                    'fieldValue' => $orderId,
                ]
            );
        }

        $appliedTaxBuilder = $this->orderTaxDetailsBuilder->getAppliedTaxBuilder();
        $orderItemAppliedTaxes = $this->orderItemTaxFactory->create()->getTaxItemsByOrderId($orderId);
        $itemsData = [];
        foreach ($orderItemAppliedTaxes as $itemAppliedTax) {
            //group applied taxes by item
            if (isset($itemAppliedTax['item_id'])) {
                //The taxable is a product
                $itemId = $itemAppliedTax['item_id'];
                if (!isset($itemsData[$itemId])) {
                    $itemsData[$itemId] = [
                        Item::KEY_ITEM_ID => $itemAppliedTax['item_id'],
                        Item::KEY_TYPE => $itemAppliedTax['taxable_item_type'],
                        Item::KEY_ASSOCIATED_ITEM_ID => null,
                    ];
                }
                $itemsData[$itemId]['applied_taxes'][$itemAppliedTax['code']] =
                    $this->convertToAppliedTaxDataObject($appliedTaxBuilder, $itemAppliedTax);
            } elseif (isset($itemAppliedTax['associated_item_id'])) {
                //The taxable is associated with a product, e.g., weee, gift wrapping etc.
                $itemId = $itemAppliedTax['associated_item_id'];
                $key = $itemAppliedTax['taxable_item_type'] . $itemId;
                if (!isset($itemsData[$key])) {
                    $itemsData[$key] = [
                        Item::KEY_ITEM_ID => null,
                        Item::KEY_TYPE => $itemAppliedTax['taxable_item_type'],
                        Item::KEY_ASSOCIATED_ITEM_ID => $itemId,
                    ];
                }
                $itemsData[$key]['applied_taxes'][$itemAppliedTax['code']] =
                    $this->convertToAppliedTaxDataObject($appliedTaxBuilder, $itemAppliedTax);

            } else {
                //The taxable is not associated with a product, e.g., shipping
                //Use item type as key
                $key = $itemAppliedTax['taxable_item_type'];
                if (!isset($itemsData[$key])) {
                    $itemsData[$key] = [
                        Item::KEY_TYPE => $itemAppliedTax['taxable_item_type'],
                        Item::KEY_ITEM_ID => null,
                        Item::KEY_ASSOCIATED_ITEM_ID => null,
                    ];
                }
                $itemsData[$key][Item::KEY_APPLIED_TAXES][$itemAppliedTax['code']] =
                    $this->convertToAppliedTaxDataObject($appliedTaxBuilder, $itemAppliedTax);
            }
        }

        $itemBuilder = $this->orderTaxDetailsBuilder->getItemBuilder();
        $items = [];
        foreach ($itemsData as $itemData) {
            $itemBuilder->setType($itemData[Item::KEY_TYPE]);
            $itemBuilder->setItemId($itemData[Item::KEY_ITEM_ID]);
            $itemBuilder->setAssociatedItemId($itemData[Item::KEY_ASSOCIATED_ITEM_ID]);
            $itemBuilder->setAppliedTaxes($itemData[Item::KEY_APPLIED_TAXES]);
            $items[] = $itemBuilder->create();
        }
        $this->orderTaxDetailsBuilder->setItems($items);
        $orderAppliedTaxesDOs = $this->aggregateAppliedTaxes($appliedTaxBuilder, $items);
        $this->orderTaxDetailsBuilder->setAppliedTaxes($orderAppliedTaxesDOs);
        return $this->orderTaxDetailsBuilder->create();
    }
}
