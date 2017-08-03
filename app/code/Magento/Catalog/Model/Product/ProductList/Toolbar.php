<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\ProductList;

/**
 * Class Toolbar
 *
 * @api
 * @since 2.0.0
 */
class Toolbar
{
    /**
     * GET parameter page variable name
     */
    const PAGE_PARM_NAME = 'p';

    /**
     * Sort order cookie name
     */
    const ORDER_PARAM_NAME = 'product_list_order';

    /**
     * Sort direction cookie name
     */
    const DIRECTION_PARAM_NAME = 'product_list_dir';

    /**
     * Sort mode cookie name
     */
    const MODE_PARAM_NAME = 'product_list_mode';

    /**
     * Products per page limit order cookie name
     */
    const LIMIT_PARAM_NAME = 'product_list_limit';

    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     * @since 2.0.0
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->request->getParam(self::ORDER_PARAM_NAME);
    }

    /**
     * Get sort direction
     *
     * @return string|bool
     * @since 2.0.0
     */
    public function getDirection()
    {
        return $this->request->getParam(self::DIRECTION_PARAM_NAME);
    }

    /**
     * Get sort mode
     *
     * @return string|bool
     * @since 2.0.0
     */
    public function getMode()
    {
        return $this->request->getParam(self::MODE_PARAM_NAME);
    }

    /**
     * Get products per page limit
     *
     * @return string|bool
     * @since 2.0.0
     */
    public function getLimit()
    {
        return $this->request->getParam(self::LIMIT_PARAM_NAME);
    }

    /**
     * Return current page from request
     *
     * @return int
     * @since 2.0.0
     */
    public function getCurrentPage()
    {
        $page = (int) $this->request->getParam(self::PAGE_PARM_NAME);
        return $page ? $page : 1;
    }
}
