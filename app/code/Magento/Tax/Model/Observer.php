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
 * @category    Magento
 * @package     Magento_Tax
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax Event Observer
 */
namespace Magento\Tax\Model;

class Observer
{
    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * @var \Magento\Tax\Model\Sales\Order\TaxFactory
     */
    protected $_orderTaxFactory;

    /**
     * @var \Magento\Tax\Model\Sales\Order\Tax\ItemFactory
     */
    protected $_taxItemFactory;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_calculation;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @var \Magento\Tax\Model\Resource\Report\TaxFactory
     */
    protected $_reportTaxFactory;

    /**
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Tax\Model\Sales\Order\TaxFactory $orderTaxFactory
     * @param \Magento\Tax\Model\Sales\Order\Tax\ItemFactory $taxItemFactory
     * @param \Magento\Tax\Model\Calculation $calculation
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Tax\Model\Resource\Report\TaxFactory $reportTaxFactory
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Tax\Model\Sales\Order\TaxFactory $orderTaxFactory,
        \Magento\Tax\Model\Sales\Order\Tax\ItemFactory $taxItemFactory,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Tax\Model\Resource\Report\TaxFactory $reportTaxFactory
    ) {
        $this->_taxData = $taxData;
        $this->_orderTaxFactory = $orderTaxFactory;
        $this->_taxItemFactory = $taxItemFactory;
        $this->_calculation = $calculation;
        $this->_locale = $locale;
        $this->_reportTaxFactory = $reportTaxFactory;
    }

    /**
     * Put quote address tax information into order
     *
     * @param \Magento\Event\Observer $observer
     */
    public function salesEventConvertQuoteAddressToOrder(\Magento\Event\Observer $observer)
    {
        $address = $observer->getEvent()->getAddress();
        $order = $observer->getEvent()->getOrder();

        $taxes = $address->getAppliedTaxes();
        if (is_array($taxes)) {
            if (is_array($order->getAppliedTaxes())) {
                $taxes = array_merge($order->getAppliedTaxes(), $taxes);
            }
            $order->setAppliedTaxes($taxes);
            $order->setConvertingFromQuote(true);
        }
    }

    /**
     * Save order tax information
     *
     * @param \Magento\Event\Observer $observer
     */
    public function salesEventOrderAfterSave(\Magento\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order->getConvertingFromQuote() || $order->getAppliedTaxIsSaved()) {
            return;
        }

        $getTaxesForItems   = $order->getQuote()->getTaxesForItems();
        $taxes              = $order->getAppliedTaxes();

        $ratesIdQuoteItemId = array();
        if (!is_array($getTaxesForItems)) {
            $getTaxesForItems = array();
        }
        foreach ($getTaxesForItems as $quoteItemId => $taxesArray) {
            foreach ($taxesArray as $rates) {
                if (count($rates['rates']) == 1) {
                    $ratesIdQuoteItemId[$rates['id']][] = array(
                        'id'        => $quoteItemId,
                        'percent'   => $rates['percent'],
                        'code'      => $rates['rates'][0]['code']
                    );
                } else {
                    $percentDelta   = $rates['percent'];
                    $percentSum     = 0;
                    foreach ($rates['rates'] as $rate) {
                        $ratesIdQuoteItemId[$rates['id']][] = array(
                            'id'        => $quoteItemId,
                            'percent'   => $rate['percent'],
                            'code'      => $rate['code']
                        );
                        $percentSum += $rate['percent'];
                    }

                    if ($percentDelta != $percentSum) {
                        $delta = $percentDelta - $percentSum;
                        foreach ($ratesIdQuoteItemId[$rates['id']] as &$rateTax) {
                            if ($rateTax['id'] == $quoteItemId) {
                                $rateTax['percent'] = (($rateTax['percent'] / $percentSum) * $delta)
                                        + $rateTax['percent'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($taxes as $id => $row) {
            foreach ($row['rates'] as $tax) {
                if (is_null($row['percent'])) {
                    $baseRealAmount = $row['base_amount'];
                } else {
                    if ($row['percent'] == 0 || $tax['percent'] == 0) {
                        continue;
                    }
                    $baseRealAmount = $row['base_amount'] / $row['percent'] * $tax['percent'];
                }
                $hidden = (isset($row['hidden']) ? $row['hidden'] : 0);
                $data = array(
                    'order_id'          => $order->getId(),
                    'code'              => $tax['code'],
                    'title'             => $tax['title'],
                    'hidden'            => $hidden,
                    'percent'           => $tax['percent'],
                    'priority'          => $tax['priority'],
                    'position'          => $tax['position'],
                    'amount'            => $row['amount'],
                    'base_amount'       => $row['base_amount'],
                    'process'           => $row['process'],
                    'base_real_amount'  => $baseRealAmount,
                );

                /** @var $orderTax \Magento\Tax\Model\Sales\Order\Tax */
                $orderTax = $this->_orderTaxFactory->create();
                $result = $orderTax->setData($data)->save();

                if (isset($ratesIdQuoteItemId[$id])) {
                    foreach ($ratesIdQuoteItemId[$id] as $quoteItemId) {
                        if ($quoteItemId['code'] == $tax['code']) {
                            $item = $order->getItemByQuoteItemId($quoteItemId['id']);
                            if ($item) {
                                $data = array(
                                    'item_id'       => $item->getId(),
                                    'tax_id'        => $result->getTaxId(),
                                    'tax_percent'   => $quoteItemId['percent']
                                );
                                /** @var $taxItem \Magento\Tax\Model\Sales\Order\Tax\Item */
                                $taxItem = $this->_taxItemFactory->create();
                                $taxItem->setData($data)->save();
                            }
                        }
                    }
                }
            }
        }

        $order->setAppliedTaxIsSaved(true);
    }

    /**
     * Add tax percent values to product collection items
     *
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Tax\Model\Observer
     */
    public function addTaxPercentToProductCollection($observer)
    {
        $helper = $this->_taxData;
        $collection = $observer->getEvent()->getCollection();
        $store = $collection->getStoreId();
        if (!$helper->needPriceConversion($store)) {
            return $this;
        }

        if ($collection->requireTaxPercent()) {
            $request = $this->_calculation->getRateRequest();
            foreach ($collection as $item) {
                if (null === $item->getTaxClassId()) {
                    $item->setTaxClassId($item->getMinimalTaxClassId());
                }
                if (!isset($classToRate[$item->getTaxClassId()])) {
                    $request->setProductClassId($item->getTaxClassId());
                    $classToRate[$item->getTaxClassId()] = $this->_calculation->getRate($request);
                }
                $item->setTaxPercent($classToRate[$item->getTaxClassId()]);
            }

        }
        return $this;
    }

    /**
     * Refresh sales tax report statistics for last day
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return \Magento\Tax\Model\Observer
     */
    public function aggregateSalesReportTaxData($schedule)
    {
        $this->_locale->emulate(0);
        $currentDate = $this->_locale->date();
        $date = $currentDate->subHour(25);
        /** @var $reportTax \Magento\Tax\Model\Resource\Report\Tax */
        $reportTax = $this->_reportTaxFactory->create();
        $reportTax->aggregate($date);
        $this->_locale->revert();
        return $this;
    }

    /**
     * Reset extra tax amounts on quote addresses before recollecting totals
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Tax\Model\Observer
     */
    public function quoteCollectTotalsBefore(\Magento\Event\Observer $observer)
    {
        /* @var $quote \Magento\Sales\Model\Quote */
        $quote = $observer->getEvent()->getQuote();
        foreach ($quote->getAllAddresses() as $address) {
            $address->setExtraTaxAmount(0);
            $address->setBaseExtraTaxAmount(0);
        }
        return $this;
    }
}
