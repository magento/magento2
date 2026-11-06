<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Product\ProductList;

/**
 * Reads product list toolbar parameters from the request
 *
 * @api
 * @since 100.0.2
 */
class Toolbar
{
    /**
     * GET parameter page variable name
     */
    public const PAGE_PARM_NAME = 'p';

    /**
     * Sort order cookie name
     */
    public const ORDER_PARAM_NAME = 'product_list_order';

    /**
     * Sort direction cookie name
     */
    public const DIRECTION_PARAM_NAME = 'product_list_dir';

    /**
     * Sort mode cookie name
     */
    public const MODE_PARAM_NAME = 'product_list_mode';

    /**
     * Products per page limit order cookie name
     */
    public const LIMIT_PARAM_NAME = 'product_list_limit';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Get sort order
     *
     * @return string|bool
     */
    public function getOrder()
    {
        return $this->request->getParam(self::ORDER_PARAM_NAME);
    }

    /**
     * Get sort direction
     *
     * @return string|bool
     */
    public function getDirection()
    {
        $direction = $this->request->getParam(self::DIRECTION_PARAM_NAME);
        return is_array($direction) ? null : $direction;
    }

    /**
     * Get sort mode
     *
     * @return string|bool
     */
    public function getMode()
    {
        $mode = $this->request->getParam(self::MODE_PARAM_NAME);
        return is_array($mode) ? null : $mode;
    }

    /**
     * Get products per page limit
     *
     * @return string|bool
     */
    public function getLimit()
    {
        $limit = $this->request->getParam(self::LIMIT_PARAM_NAME);
        return is_array($limit) ? null : $limit;
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        $page = (int) $this->request->getParam(self::PAGE_PARM_NAME);
        return $page ?: 1;
    }
}
