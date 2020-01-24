<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Dashboard;

use Magento\Backend\Helper\Dashboard\Data as DataHelper;
use Magento\Backend\Helper\Dashboard\Order as OrderHelper;
use Magento\Backend\Model\Dashboard\Chart\Date;
use Magento\Framework\App\RequestInterface;

/**
 * Dashboard chart data retriever
 */
class Chart
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Date
     */
    private $dateRetriever;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * Chart constructor.
     * @param RequestInterface $request
     * @param Date $dateRetriever
     * @param OrderHelper $orderHelper
     * @param DataHelper $dataHelper
     */
    public function __construct(
        RequestInterface $request,
        Date $dateRetriever,
        OrderHelper $orderHelper,
        DataHelper $dataHelper
    ) {
        $this->request = $request;
        $this->dateRetriever = $dateRetriever;
        $this->orderHelper = $orderHelper;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Get chart data by period and chart type parameter
     *
     * @param string $period
     * @param string $chartParam
     *
     * @return array
     */
    public function getByPeriod($period, $chartParam): array
    {
        $this->orderHelper->setParam('store', $this->request->getParam('store'));
        $this->orderHelper->setParam('website', $this->request->getParam('website'));
        $this->orderHelper->setParam('group', $this->request->getParam('group'));

        $availablePeriods = array_keys($this->dataHelper->getDatePeriods());
        $this->orderHelper->setParam(
            'period',
            $period && in_array($period, $availablePeriods, false) ? $period : '24h'
        );

        $dates = $this->dateRetriever->getByPeriod($period);
        $collection = $this->orderHelper->getCollection();

        $data = [];

        if ($collection->count() > 0) {
            foreach ($dates as $date) {
                $item = $collection->getItemByColumnValue('range', $date);

                $data[] = [
                    'x' => $date,
                    'y' => $item ? (float)$item->getData($chartParam) : 0
                ];
            }
        }

        return $data;
    }
}
