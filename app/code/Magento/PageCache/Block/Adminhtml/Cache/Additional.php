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
 * @category    Magento
 * @package     Magento_PageCache
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System cache management additional block
 *
 * @category    Magento
 * @package     Magento_PageCache
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\PageCache\Block\Adminhtml\Cache;

class Additional extends \Magento\Backend\Block\Template
{
    /**
     * Page cache data
     *
     * @var \Magento\PageCache\Helper\Data
     */
    protected $_pageCacheData = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\PageCache\Helper\Data $pageCacheData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\PageCache\Helper\Data $pageCacheData,
        array $data = array()
    ) {
        $this->_pageCacheData = $pageCacheData;
        parent::__construct($context, $coreData, $data);
    }

    /**
     * Get clean cache url
     *
     * @return string
     */
    public function getCleanExternalCacheUrl()
    {
        return $this->getUrl('adminhtml/pageCache/clean');
    }

    /**
     * Check if block can be displayed
     *
     * @return bool
     */
    public function canShowButton()
    {
        return $this->_pageCacheData->isEnabled()
            && $this->_authorization->isAllowed('Magento_PageCache::page_cache');
    }
}
