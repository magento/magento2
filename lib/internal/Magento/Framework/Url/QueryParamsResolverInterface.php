<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

interface QueryParamsResolverInterface
{
    /**
     * Get query params part of url
     *
     * @param bool $escape "&" escape flag
     * @return string
     */
    public function getQuery($escape = false);

    /**
     * Set URL query param(s)
     *
     * @param mixed $data
     * @return \Magento\Framework\Url\QueryParamsResolverInterface
     */
    public function setQuery($data);

    /**
     * Set query param
     *
     * @param string $key
     * @param mixed $data
     * @return \Magento\Framework\Url\QueryParamsResolverInterface
     */
    public function setQueryParam($key, $data);

    /**
     * Return Query Params
     *
     * @return array
     */
    public function getQueryParams();

    /**
     * Set query parameters
     *
     * @param array $data
     * @return \Magento\Framework\Url\QueryParamsResolverInterface
     */
    public function setQueryParams(array $data);

    /**
     * Add query parameters
     *
     * @param array $data
     * @return \Magento\Framework\Url\QueryParamsResolverInterface
     */
    public function addQueryParams(array $data);

    /**
     * Unset data from the object.
     *
     * @param null|string|array $key
     * @return \Magento\Framework\Object
     */
    public function unsetData($key = null);
}
