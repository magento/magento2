<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\Store;

use Magento\Backend\Block\Store\Switcher as StoreSwitcherBlock;
use Magento\Paypal\Model\Config\StructurePlugin as ConfigStructurePlugin;

/**
 * Plugin for \Magento\Backend\Block\Store\Switcher
 */
class SwitcherPlugin
{
    /**
     * Remove country request param from url
     *
     * @param StoreSwitcherBlock $subject
     * @param string $route
     * @param array $params
     * @return array
     */
    public function beforeGetUrl(StoreSwitcherBlock $subject, $route = '', $params = [])
    {
        if ($subject->getRequest()->getParam(ConfigStructurePlugin::REQUEST_PARAM_COUNTRY)) {
            $params[ConfigStructurePlugin::REQUEST_PARAM_COUNTRY] = null;
        }

        return [$route, $params];
    }
}
