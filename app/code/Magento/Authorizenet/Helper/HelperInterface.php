<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Authorizenet\Helper;

/**
 * Authorizenet Data Helper
 */
interface HelperInterface
{
    /**
     * Retrieve place order url
     *
     * @param array $params
     * @return  string
     */
    public function getSuccessOrderUrl($params);

    /**
     * Retrieve redirect ifrmae url
     *
     * @param array $params
     * @return string
     */
    public function getRedirectIframeUrl($params);

    /**
     * Get direct post rely url
     *
     * @param null|int|string $storeId
     * @return string
     */
    public function getRelyUrl($storeId = null);
}
