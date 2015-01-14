<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Check is link shareable or not
     *
     * @param \Magento\Downloadable\Model\Link|Item $link
     * @return bool
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
                $shareable = (bool)$this->_scopeConfig->isSetFlag(
                    \Magento\Downloadable\Model\Link::XML_PATH_CONFIG_IS_SHAREABLE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
        }
        return $shareable;
    }
}
