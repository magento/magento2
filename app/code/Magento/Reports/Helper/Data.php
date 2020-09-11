<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Helper;

use Magento\Framework\Data\Collection;

/**
 * Reports data helper.
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const REPORT_PERIOD_TYPE_DAY = 'day';

    const REPORT_PERIOD_TYPE_MONTH = 'month';

    const REPORT_PERIOD_TYPE_YEAR = 'year';

    /**
     * Item factory
     *
     * @var \Magento\Reports\Model\ItemFactory
     */
    protected $_itemFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Reports\Model\ItemFactory $itemFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Reports\Model\ItemFactory $itemFactory
    ) {
        parent::__construct($context);
        $this->_itemFactory = $itemFactory;
    }

    /**
     * Retrieve array of intervals
     *
     * @param string $from
     * @param string $to
     * @param string $period
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getIntervals($from, $to, $period = self::REPORT_PERIOD_TYPE_DAY)
    {
        $intervals = [];
        if (!$from && !$to) {
            return $intervals;
        }

        $dateStart = new \DateTime($from);
        $dateEnd = new \DateTime($to);
        $dateFormat = 'Y-m-d';
        $dateInterval = new \DateInterval('P1D');
        switch ($period) {
            case self::REPORT_PERIOD_TYPE_MONTH:
                $dateFormat = 'Y-m';
                $dateInterval = new \DateInterval('P1M');
                break;
            case self::REPORT_PERIOD_TYPE_YEAR:
                $dateFormat = 'Y';
                $dateInterval = new \DateInterval('P1Y');
                break;
        }
        while ($dateStart->diff($dateEnd)->invert == 0) {
            $intervals[] = $dateStart->format($dateFormat);
            $dateStart->add($dateInterval);
        }

        if (!in_array($dateEnd->format($dateFormat), $intervals)) {
            $intervals[] = $dateEnd->format($dateFormat);
        }

        return $intervals;
    }

    /**
     * Add items to interval collection
     *
     * @param Collection $collection
     * @param string $from
     * @param string $to
     * @param string $periodType
     * @return void
     */
    public function prepareIntervalsCollection($collection, $from, $to, $periodType = self::REPORT_PERIOD_TYPE_DAY)
    {
        $intervals = $this->getIntervals($from, $to, $periodType);

        foreach ($intervals as $interval) {
            $item = $this->_itemFactory->create();
            $item->setPeriod($interval);
            $item->setIsEmpty();
            $collection->addItem($item);
        }
    }
}
