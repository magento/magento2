<?php
/**
 * Abstract class that represents profiler standard driver output
 *
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
namespace Magento\Framework\Profiler\Driver\Standard;

abstract class AbstractOutput implements OutputInterface
{
    /**
     * PCRE Regular Expression for filter timer by id
     *
     * @var null|string
     */
    protected $_filterPattern;

    /**
     * List of threshold (minimal allowed) values for profiler data
     *
     * @var array
     */
    protected $_thresholds = array(Stat::TIME => 0.001, Stat::COUNT => 10, Stat::EMALLOC => 10000);

    /**
     * List of columns to output
     *
     * @var array
     */
    protected $_columns = array(
        'Timer Id' => Stat::ID,
        'Time' => Stat::TIME,
        'Avg' => Stat::AVG,
        'Cnt' => Stat::COUNT,
        'Emalloc' => Stat::EMALLOC,
        'RealMem' => Stat::REALMEM
    );

    /**
     * Constructor
     *
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        if (!empty($config['filterPattern'])) {
            $this->setFilterPattern($config['filterPattern']);
        }
        if (!empty($config['thresholds']) && is_array($config['thresholds'])) {
            foreach ($config['thresholds'] as $fetchKey => $minAllowedValue) {
                $this->setThreshold($fetchKey, (int)$minAllowedValue);
            }
        }
    }

    /**
     * Set profiler output with timer identifiers filter.
     *
     * @param string $filterPattern PCRE pattern to filter timers by their identifiers
     * @return void
     */
    public function setFilterPattern($filterPattern)
    {
        $this->_filterPattern = $filterPattern;
    }

    /**
     * Get profiler output timer identifiers filter.
     *
     * @return string|null
     */
    public function getFilterPattern()
    {
        return $this->_filterPattern;
    }

    /**
     * Set threshold (minimal allowed) value for timer column.
     *
     * Timer is being rendered if at least one of its columns is not less than the minimal allowed value.
     *
     * @param string $fetchKey
     * @param int|float|null $minAllowedValue
     * @return void
     */
    public function setThreshold($fetchKey, $minAllowedValue)
    {
        if ($minAllowedValue === null) {
            unset($this->_thresholds[$fetchKey]);
        } else {
            $this->_thresholds[$fetchKey] = $minAllowedValue;
        }
    }

    /**
     * Get list of thresholds.
     *
     * @return array
     */
    public function getThresholds()
    {
        return $this->_thresholds;
    }

    /**
     * Render statistics column value for specified timer
     *
     * @param string|float $value
     * @param string $columnKey
     * @return string
     */
    protected function _renderColumnValue($value, $columnKey)
    {
        switch ($columnKey) {
            case Stat::ID:
                $result = $this->_renderTimerId($value);
                break;
            case Stat::TIME:
            case Stat::AVG:
                $result = number_format($value, 6);
                break;
            default:
                $result = number_format((string)$value);
        }
        return $result;
    }

    /**
     * Render timer id
     *
     * @param string $timerId
     * @return string
     */
    protected function _renderTimerId($timerId)
    {
        return $timerId;
    }

    /**
     * Render a caption for the profiling results
     *
     * @return string
     */
    protected function _renderCaption()
    {
        return sprintf(
            'Code Profiler (Memory usage: real - %s, emalloc - %s)',
            memory_get_usage(true),
            memory_get_usage()
        );
    }

    /**
     * Retrieve the list of timer ids from timer statistics object.
     *
     * Timer ids will be ordered and filtered by thresholds and filter pattern.
     *
     * @param Stat $stat
     * @return string[]
     */
    protected function _getTimerIds(Stat $stat)
    {
        return $stat->getFilteredTimerIds($this->_thresholds, $this->_filterPattern);
    }
}
