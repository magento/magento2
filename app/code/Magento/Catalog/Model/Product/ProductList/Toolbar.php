<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\ProductList;

use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Class Toolbar
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
    const ORDER_COOKIE_NAME = 'product_list_order';

    /**
     * Sort direction cookie name
     */
    const DIRECTION_COOKIE_NAME = 'product_list_dir';

    /**
     * Sort mode cookie name
     */
    const MODE_COOKIE_NAME = 'product_list_mode';

    /**
     * Products per page limit order cookie name
     */
    const LIMIT_COOKIE_NAME = 'product_list_limit';

    /**
     * Cookie manager
     *
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->cookieManager = $cookieManager;
        $this->request = $request;
    }

    /**
     * Get sort order
     *
     * @return string|bool
     */
    public function getOrder()
    {
        return $this->cookieManager->getCookie(self::ORDER_COOKIE_NAME);
    }

    /**
     * Get sort direction
     *
     * @return string|bool
     */
    public function getDirection()
    {
        return $this->cookieManager->getCookie(self::DIRECTION_COOKIE_NAME);
    }

    /**
     * Get sort mode
     *
     * @return string|bool
     */
    public function getMode()
    {
        return $this->cookieManager->getCookie(self::MODE_COOKIE_NAME);
    }

    /**
     * Get products per page limit
     *
     * @return string|bool
     */
    public function getLimit()
    {
        return $this->cookieManager->getCookie(self::LIMIT_COOKIE_NAME);
    }
    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        $page = (int) $this->request->getParam(self::PAGE_PARM_NAME);
        return $page ? $page : 1;
    }
}
