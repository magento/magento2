<?php
/**
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Store\Model;

/**
 * Store resolver interface
 *
 * @api
 */
interface StoreResolverInterface
{
    /**
     * Retrieve current store code
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookie
     * @return string
     */
    public function getCurrentStoreId(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookie
    );
}
