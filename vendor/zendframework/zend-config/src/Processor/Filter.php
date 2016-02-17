<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config\Processor;

use Zend\Config\Config;
use Zend\Config\Exception;
use Zend\Filter\FilterInterface as ZendFilter;

class Filter implements ProcessorInterface
{
    /**
     * @var ZendFilter
     */
    protected $filter;

    /**
     * Filter all config values using the supplied Zend\Filter
     *
     * @param ZendFilter $filter
     */
    public function __construct(ZendFilter $filter)
    {
        $this->setFilter($filter);
    }

    /**
     * @param  ZendFilter $filter
     * @return Filter
     */
    public function setFilter(ZendFilter $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return ZendFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Process
     *
     * @param  Config $config
     * @return Config
     * @throws Exception\InvalidArgumentException
     */
    public function process(Config $config)
    {
        if ($config->isReadOnly()) {
            throw new Exception\InvalidArgumentException('Cannot process config because it is read-only');
        }

        /**
         * Walk through config and replace values
         */
        foreach ($config as $key => $val) {
            if ($val instanceof Config) {
                $this->process($val);
            } else {
                $config->$key = $this->filter->filter($val);
            }
        }

        return $config;
    }

    /**
     * Process a single value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function processValue($value)
    {
        return $this->filter->filter($value);
    }
}
