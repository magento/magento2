<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Resolves query parameters in a URL.
 *
 * @api
 * @since 2.0.0
 */
interface QueryParamsResolverInterface
{
    /**
     * Get query params part of url
     *
     * @param bool $escape "&" escape flag
     * @return string
     * @since 2.0.0
     */
    public function getQuery($escape = false);

    /**
     * Set URL query param(s)
     *
     * @param mixed $data
     * @return \Magento\Framework\Url\QueryParamsResolverInterface
     * @since 2.0.0
     */
    public function setQuery($data);

    /**
     * Set query param
     *
     * @param string $key
     * @param mixed $data
     * @return \Magento\Framework\Url\QueryParamsResolverInterface
     * @since 2.0.0
     */
    public function setQueryParam($key, $data);

    /**
     * Return Query Params
     *
     * @return array
     * @since 2.0.0
     */
    public function getQueryParams();

    /**
     * Set query parameters
     *
     * @param array $data
     * @return \Magento\Framework\Url\QueryParamsResolverInterface
     * @since 2.0.0
     */
    public function setQueryParams(array $data);

    /**
     * Add query parameters
     *
     * @param array $data
     * @return \Magento\Framework\Url\QueryParamsResolverInterface
     * @since 2.0.0
     */
    public function addQueryParams(array $data);

    /**
     * Unset data from the object.
     *
     * @param null|string|array $key
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function unsetData($key = null);
}
