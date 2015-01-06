<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Quote\Address\Total;

/**
 * Nominal items total
 *
 * Collects only items segregated by isNominal property
 * Aggregates row totals per item
 */
class Nominal extends \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Sales\Model\Quote\Address\Total\Nominal\CollectorFactory
     */
    protected $_collectorFactory;

    /**
     * @param \Magento\Sales\Model\Quote\Address\Total\Nominal\CollectorFactory $collectorFactory
     */
    public function __construct(\Magento\Sales\Model\Quote\Address\Total\Nominal\CollectorFactory $collectorFactory)
    {
        $this->_collectorFactory = $collectorFactory;
    }

    /**
     * Invoke collector for nominal items
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        $collector = $this->_collectorFactory->create(['store' => $address->getQuote()->getStore()]);

        // invoke nominal totals
        foreach ($collector->getCollectors() as $model) {
            $model->collect($address);
        }

        // aggregate collected amounts into one to have sort of grand total per item
        foreach ($address->getAllNominalItems() as $item) {
            $rowTotal = 0;
            $baseRowTotal = 0;
            $totalDetails = [];
            foreach ($collector->getCollectors() as $model) {
                $itemRowTotal = $model->getItemRowTotal($item);
                if ($model->getIsItemRowTotalCompoundable($item)) {
                    $rowTotal += $itemRowTotal;
                    $baseRowTotal += $model->getItemBaseRowTotal($item);
                    $isCompounded = true;
                } else {
                    $isCompounded = false;
                }
                if ((double)$itemRowTotal > 0 && ($label = $model->getLabel())) {
                    $totalDetails[] = new \Magento\Framework\Object(
                        ['label' => $label, 'amount' => $itemRowTotal, 'is_compounded' => $isCompounded]
                    );
                }
            }
            $item->setNominalRowTotal($rowTotal);
            $item->setBaseNominalRowTotal($baseRowTotal);
            $item->setNominalTotalDetails($totalDetails);
        }

        return $this;
    }

    /**
     * Fetch collected nominal items
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function fetch(\Magento\Sales\Model\Quote\Address $address)
    {
        $items = $address->getAllNominalItems();
        if ($items) {
            $address->addTotal(
                [
                    'code' => $this->getCode(),
                    'title' => __('Subscription Items'),
                    'items' => $items,
                    'area' => 'footer',
                ]
            );
        }
        return $this;
    }
}
