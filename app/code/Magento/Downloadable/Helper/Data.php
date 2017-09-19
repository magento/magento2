<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Helper;

use Magento\Downloadable\Model\Link\Purchased\Item;

/**
 * Downloadable helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Check is link shareable or not
     *
     * @param \Magento\Downloadable\Model\Link|Item $link
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsShareable($link)
    {
        $shareable = false;
        switch ($link->getIsShareable()) {
            case \Magento\Downloadable\Model\Link::LINK_SHAREABLE_YES:
            case \Magento\Downloadable\Model\Link::LINK_SHAREABLE_NO:
                $shareable = (bool)$link->getIsShareable();
                break;
            case \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG:
                $shareable = (bool)$this->scopeConfig->isSetFlag(
                    \Magento\Downloadable\Model\Link::XML_PATH_CONFIG_IS_SHAREABLE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
        }
        return $shareable;
    }
}
