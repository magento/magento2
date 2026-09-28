<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Downloadable\Helper;

use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Store\Model\ScopeInterface;

/**
 * Downloadable helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Check is link shareable or not
     *
     * @param Link|Item $link
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsShareable($link)
    {
        $shareable = false;
        switch ($link->getIsShareable()) {
            case Link::LINK_SHAREABLE_YES:
            case Link::LINK_SHAREABLE_NO:
                $shareable = (bool)$link->getIsShareable();
                break;
            case Link::LINK_SHAREABLE_CONFIG:
                $shareable = $this->scopeConfig->isSetFlag(
                    Link::XML_PATH_CONFIG_IS_SHAREABLE,
                    ScopeInterface::SCOPE_STORE
                );
        }
        return $shareable;
    }
}
