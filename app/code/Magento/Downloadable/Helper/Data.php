<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
