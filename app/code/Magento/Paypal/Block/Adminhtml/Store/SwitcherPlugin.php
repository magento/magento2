<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\Store;

class SwitcherPlugin
{
    /**
     * Remove country request param from url
     *
     * @param \Magento\Backend\Block\Store\Switcher $subject
     * @param \Closure $proceed
     * @param string $route
     * @param array $params
     * @return string
     */
    public function aroundGetUrl(
        \Magento\Backend\Block\Store\Switcher $subject,
        \Closure $proceed,
        $route = '',
        $params = []
    ) {
        if ($subject->getRequest()->getParam(\Magento\Paypal\Model\Config\StructurePlugin::REQUEST_PARAM_COUNTRY)) {
            $params[\Magento\Paypal\Model\Config\StructurePlugin::REQUEST_PARAM_COUNTRY] = null;
        }
        return $proceed($route, $params);
    }
}
