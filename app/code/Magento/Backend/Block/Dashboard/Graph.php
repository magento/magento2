<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard;

/**
 * Adminhtml dashboard google chart block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Graph extends \Magento\Backend\Block\Dashboard\AbstractDashboard
{
    /**
     * Api URL
     */
    const API_URL = 'http://chart.apis.google.com/chart';

    /**
     * All series
     *
     * @var array
     * @since 2.0.0
     */
    protected $_allSeries = [];

    /**
     * Axis labels
     *
     * @var array
     * @since 2.0.0
     */
    protected $_axisLabels = [];

    /**
     * Axis maps
     *
     * @var array
     * @since 2.0.0
     */
    protected $_axisMaps = [];

    /**
     * Data rows
     *
     * @var array
     * @since 2.0.0
     */
    protected $_dataRows = [];

    /**
     * Simple encoding chars
     *
     * @var string
     * @since 2.0.0
     */
    protected $_simpleEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * Extended encoding chars
     *
     * @var string
     * @since 2.0.0
     */
    protected $_extendedEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';

    /**
     * Chart width
     *
     * @var string
     * @since 2.0.0
     */
    protected $_width = '780';

    /**
     * Chart height
     *
     * @var string
     * @since 2.0.0
     */
    protected $_height = '384';

    /**
     * Google chart api data encoding
     *
     * @var string
     * @since 2.0.0
     */
    protected $_encoding = 'e';

    /**
     * Html identifier
     *
     * @var string
     * @since 2.0.0
     */
    protected $_htmlId = '';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'dashboard/graph.phtml';

    /**
     * Adminhtml dashboard data
     *
     * @var \Magento\Backend\Helper\Dashboard\Data
     * @since 2.0.0
     */
    protected $_dashboardData = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Dashboard\Data $dashboardData
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Dashboard\Data $dashboardData,
        array $data = []
    ) {
        $this->_dashboardData = $dashboardData;
        parent::__construct($context, $collectionFactory, $data);
    }

    /**
     * Get tab template
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getTabTemplate()
    {
        return 'dashboard/graph.phtml';
    }

    /**
     * Set data rows
     *
     * @param array $rows
     * @return void
     * @since 2.0.0
     */
    public function setDataRows($rows)
    {
        $this->_dataRows = (array)$rows;
    }

    /**
     * Add series
     *
     * @param string $seriesId
     * @param array $options
     * @return void
     * @since 2.0.0
     */
    public function addSeries($seriesId, array $options)
    {
        $this->_allSeries[$seriesId] = $options;
    }

    /**
     * Get series
     *
     * @param string $seriesId
     * @return array|false
     * @since 2.0.0
     */
    public function getSeries($seriesId)
    {
        if (isset($this->_allSeries[$seriesId])) {
            return $this->_allSeries[$seriesId];
        } else {
            return false;
        }
    }

    /**
     * Get all series
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllSeries()
    {
        return $this->_allSeries;
    }

    /**
     * Get chart url
     *
     * @param bool $directUrl
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
     */
    public function getChartUrl($directUrl = true)
    {
        $params = [
            'cht' => 'lc',
            'chf' => 'bg,s,ffffff',
            'chco' => 'ef672f',
            'chls' => '7',
            'chxs' => '0,676056,15,0,l,676056|1,676056,15,0,l,676056',
            'chm' => 'h,f2ebde,0,0:1:.1,1,-1',
        ];

        $this->_allSeries = $this->getRowsData($this->_dataRows);

        foreach ($this->_axisMaps as $axis => $attr) {
            $this->setAxisLabels($axis, $this->getRowsData($attr, true));
        }

        $timezoneLocal = $this->_localeDate->getConfigTimezone();

        /** @var \DateTime $dateStart */
        /** @var \DateTime $dateEnd */
        list($dateStart, $dateEnd) = $this->_collectionFactory->create()->getDateRange(
            $this->getDataHelper()->getParam('period'),
            '',
            '',
            true
        );

        $dateStart->setTimezone(new \DateTimeZone($timezoneLocal));
        $dateEnd->setTimezone(new \DateTimeZone($timezoneLocal));

        if ($this->getDataHelper()->getParam('period') == '24h') {
            $dateEnd->modify('-1 hour');
        } else {
            $dateEnd->setTime(23, 59, 59);
            $dateStart->setTime(0, 0, 0);
        }

        $dates = [];
        $datas = [];

        while ($dateStart <= $dateEnd) {
            switch ($this->getDataHelper()->getParam('period')) {
                case '7d':
                case '1m':
                    $d = $dateStart->format('Y-m-d');
                    $dateStart->modify('+1 day');
                    break;
                case '1y':
                case '2y':
                    $d = $dateStart->format('Y-m');
                    $dateStart->modify('+1 month');
                    break;
                default:
                    $d = $dateStart->format('Y-m-d H:00');
                    $dateStart->modify('+1 hour');
            }
            foreach ($this->getAllSeries() as $index => $serie) {
                if (in_array($d, $this->_axisLabels['x'])) {
                    $datas[$index][] = (double)array_shift($this->_allSeries[$index]);
                } else {
                    $datas[$index][] = 0;
                }
            }
            $dates[] = $d;
        }

        /**
         * setting skip step
         */
        if (count($dates) > 8 && count($dates) < 15) {
            $c = 1;
        } else {
            if (count($dates) >= 15) {
                $c = 2;
            } else {
                $c = 0;
            }
        }
        /**
         * skipping some x labels for good reading
         */
        $i = 0;
        foreach ($dates as $k => $d) {
            if ($i == $c) {
                $dates[$k] = $d;
                $i = 0;
            } else {
                $dates[$k] = '';
                $i++;
            }
        }

        $this->_axisLabels['x'] = $dates;
        $this->_allSeries = $datas;

        //Google encoding values
        if ($this->_encoding == "s") {
            // simple encoding
            $params['chd'] = "s:";
            $dataDelimiter = "";
            $dataSetdelimiter = ",";
            $dataMissing = "_";
        } else {
            // extended encoding
            $params['chd'] = "e:";
            $dataDelimiter = "";
            $dataSetdelimiter = ",";
            $dataMissing = "__";
        }

        // process each string in the array, and find the max length
        $localmaxvalue = [0];
        $localminvalue = [0];
        foreach ($this->getAllSeries() as $index => $serie) {
            $localmaxvalue[$index] = max($serie);
            $localminvalue[$index] = min($serie);
        }

        $maxvalue = max($localmaxvalue);
        $minvalue = min($localminvalue);

        // default values
        $yrange = 0;
        $yLabels = [];
        $miny = 0;
        $maxy = 0;
        $yorigin = 0;

        if ($minvalue >= 0 && $maxvalue >= 0) {
            if ($maxvalue > 10) {
                $p = pow(10, $this->_getPow($maxvalue));
                $maxy = ceil($maxvalue / $p) * $p;
                $yLabels = range($miny, $maxy, $p);
            } else {
                $maxy = ceil($maxvalue + 1);
                $yLabels = range($miny, $maxy, 1);
            }
            $yrange = $maxy;
            $yorigin = 0;
        }

        $chartdata = [];

        foreach ($this->getAllSeries() as $index => $serie) {
            $thisdataarray = $serie;
            if ($this->_encoding == "s") {
                // SIMPLE ENCODING
                for ($j = 0; $j < sizeof($thisdataarray); $j++) {
                    $currentvalue = $thisdataarray[$j];
                    if (is_numeric($currentvalue)) {
                        $ylocation = round(
                            (strlen($this->_simpleEncoding) - 1) * ($yorigin + $currentvalue) / $yrange
                        );
                        $chartdata[] = substr($this->_simpleEncoding, $ylocation, 1) . $dataDelimiter;
                    } else {
                        $chartdata[] = $dataMissing . $dataDelimiter;
                    }
                }
            } else {
                // EXTENDED ENCODING
                for ($j = 0; $j < sizeof($thisdataarray); $j++) {
                    $currentvalue = $thisdataarray[$j];
                    if (is_numeric($currentvalue)) {
                        if ($yrange) {
                            $ylocation = 4095 * ($yorigin + $currentvalue) / $yrange;
                        } else {
                            $ylocation = 0;
                        }
                        $firstchar = floor($ylocation / 64);
                        $secondchar = $ylocation % 64;
                        $mappedchar = substr(
                            $this->_extendedEncoding,
                            $firstchar,
                            1
                        ) . substr(
                            $this->_extendedEncoding,
                            $secondchar,
                            1
                        );
                        $chartdata[] = $mappedchar . $dataDelimiter;
                    } else {
                        $chartdata[] = $dataMissing . $dataDelimiter;
                    }
                }
            }
            $chartdata[] = $dataSetdelimiter;
        }
        $buffer = implode('', $chartdata);

        $buffer = rtrim($buffer, $dataSetdelimiter);
        $buffer = rtrim($buffer, $dataDelimiter);
        $buffer = str_replace($dataDelimiter . $dataSetdelimiter, $dataSetdelimiter, $buffer);

        $params['chd'] .= $buffer;

        $valueBuffer = [];

        if (sizeof($this->_axisLabels) > 0) {
            $params['chxt'] = implode(',', array_keys($this->_axisLabels));
            $indexid = 0;
            foreach ($this->_axisLabels as $idx => $labels) {
                if ($idx == 'x') {
                    /**
                     * Format date
                     */
                    foreach ($this->_axisLabels[$idx] as $_index => $_label) {
                        if ($_label != '') {
                            $period = new \DateTime($_label, new \DateTimeZone($timezoneLocal));
                            switch ($this->getDataHelper()->getParam('period')) {
                                case '24h':
                                    $this->_axisLabels[$idx][$_index] = $this->_localeDate->formatDateTime(
                                        $period->setTime($period->format('H'), 0, 0),
                                        \IntlDateFormatter::NONE,
                                        \IntlDateFormatter::SHORT
                                    );
                                    break;
                                case '7d':
                                case '1m':
                                    $this->_axisLabels[$idx][$_index] = $this->_localeDate->formatDateTime(
                                        $period,
                                        \IntlDateFormatter::SHORT,
                                        \IntlDateFormatter::NONE
                                    );
                                    break;
                                case '1y':
                                case '2y':
                                    $this->_axisLabels[$idx][$_index] = date('m/Y', strtotime($_label));
                                    break;
                            }
                        } else {
                            $this->_axisLabels[$idx][$_index] = '';
                        }
                    }

                    $tmpstring = implode('|', $this->_axisLabels[$idx]);

                    $valueBuffer[] = $indexid . ":|" . $tmpstring;
                }
                $indexid++;
            }
            $params['chxl'] = implode('|', $valueBuffer);
        }

        // chart size
        $params['chs'] = $this->getWidth() . 'x' . $this->getHeight();

        // return the encoded data
        if ($directUrl) {
            $p = [];
            foreach ($params as $name => $value) {
                $p[] = $name . '=' . urlencode($value);
            }
            return self::API_URL . '?' . implode('&', $p);
        } else {
            $gaData = urlencode(base64_encode(json_encode($params)));
            $gaHash = $this->_dashboardData->getChartDataHash($gaData);
            $params = ['ga' => $gaData, 'h' => $gaHash];
            return $this->getUrl('adminhtml/*/tunnel', ['_query' => $params]);
        }
    }

    /**
     * Get rows data
     *
     * @param array $attributes
     * @param bool $single
     * @return array
     * @since 2.0.0
     */
    protected function getRowsData($attributes, $single = false)
    {
        $items = $this->getCollection()->getItems();
        $options = [];
        foreach ($items as $item) {
            if ($single) {
                $options[] = max(0, $item->getData($attributes));
            } else {
                foreach ((array)$attributes as $attr) {
                    $options[$attr][] = max(0, $item->getData($attr));
                }
            }
        }
        return $options;
    }

    /**
     * Set axis labels
     *
     * @param string $axis
     * @param array $labels
     * @return void
     * @since 2.0.0
     */
    public function setAxisLabels($axis, $labels)
    {
        $this->_axisLabels[$axis] = $labels;
    }

    /**
     * Set html id
     *
     * @param string $htmlId
     * @return void
     * @since 2.0.0
     */
    public function setHtmlId($htmlId)
    {
        $this->_htmlId = $htmlId;
    }

    /**
     * Get html id
     *
     * @return string
     * @since 2.0.0
     */
    public function getHtmlId()
    {
        return $this->_htmlId;
    }

    /**
     * Return pow
     *
     * @param int $number
     * @return int
     * @since 2.0.0
     */
    protected function _getPow($number)
    {
        $pow = 0;
        while ($number >= 10) {
            $number = $number / 10;
            $pow++;
        }
        return $pow;
    }

    /**
     * Return chart width
     *
     * @return string
     * @since 2.0.0
     */
    protected function getWidth()
    {
        return $this->_width;
    }

    /**
     * Return chart height
     *
     * @return string
     * @since 2.0.0
     */
    protected function getHeight()
    {
        return $this->_height;
    }

    /**
     * @param \Magento\Backend\Helper\Dashboard\AbstractDashboard $dataHelper
     * @return void
     * @since 2.0.0
     */
    public function setDataHelper(\Magento\Backend\Helper\Dashboard\AbstractDashboard $dataHelper)
    {
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Prepare chart data
     *
     * @return void
     * @since 2.0.0
     */
    protected function _prepareData()
    {
        if ($this->_dataHelper !== null) {
            $availablePeriods = array_keys($this->_dashboardData->getDatePeriods());
            $period = $this->getRequest()->getParam('period');
            $this->getDataHelper()->setParam(
                'period',
                $period && in_array($period, $availablePeriods) ? $period : '24h'
            );
        }
    }
}
