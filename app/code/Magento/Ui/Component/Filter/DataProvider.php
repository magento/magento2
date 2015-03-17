<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filter;

use Magento\Framework\App\RequestInterface;

/**
 * Class DataProvider
 */
class DataProvider
{
    /**
     * Filter variable name
     */
    const FILTER_VAR = 'filter';

    /**
     * Filter data
     *
     * @var array
     */
    protected $filterData;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->filterData = $this->prepareFilterString($request->getParam(static::FILTER_VAR));
    }

    /**
     * Get filter data
     *
     * @param string $name
     * @return string
     */
    public function getData($name)
    {
        return isset($this->filterData[$name]) ? $this->filterData[$name] : null;
    }

    /**
     * Decode filter string
     *
     * @param string $filterString
     * @return array
     */
    protected function prepareFilterString($filterString)
    {
        $data = [];
        $filterString = base64_decode($filterString);
        parse_str($filterString, $data);
        array_walk_recursive(
            $data,
            // @codingStandardsIgnoreStart
            /**
             * Decodes URL-encoded string and trims whitespaces from the beginning and end of a string
             *
             * @param string $value
             */
            // @codingStandardsIgnoreEnd
            function (&$value) {
                $value = trim(rawurldecode($value));
            }
        );

        return $data;
    }
}
