<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Plugin;

class GetUrl
{
    /**
     * A unique store parameter need to pass in \Magento\Email\Model\AbstractTemplate `getUrl` function
     * for separating links in email content for sales order email confirmation.
     *
     * @param \Magento\Email\Model\AbstractTemplate $subject
     * @param \Magento\Store\Model\Store $store
     * @param string $scope
     * @return array
     */
    public function beforeGetUrl(
        \Magento\Email\Model\AbstractTemplate $subject,
        \Magento\Store\Model\Store $store,
        $route = '',
        $params = []
    ) {
        /**
         * Pass extra parameter to distinguish stores urls for property \Magento\Email\Model\AbstractTemplate `getUrl`
         * in multi-store environment
         */
        $params['_escape_params'] = $store->getCode();

        return [$store, $route, $params];
    }
}
