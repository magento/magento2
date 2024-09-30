<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Plugin;

class GetUrl
{
    /**
     * Generate unique Urls/links separated by store in \Magento\Email\Model\AbstractTemplate `getUrl` function.
     *
     * @param \Magento\Email\Model\AbstractTemplate $subject
     * @param \Magento\Store\Model\Store $store
     * @param string $route
     * @param array $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
