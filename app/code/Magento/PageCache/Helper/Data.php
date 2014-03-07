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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Page cache data helper
 *
 * @category    Magento
 * @package     Magento_PageCache
 */
namespace Magento\PageCache\Helper;

/**
 * Helper for Page Cache module
 */
class Data extends \Magento\App\Helper\AbstractHelper
{
    /**
     * Constructor
     *
     * @param \Magento\Theme\Model\Layout\Config $config
     * @param \Magento\App\View                  $view
     */
    public function __construct(
        \Magento\Theme\Model\Layout\Config $config,
        \Magento\App\View $view
    ) {
        $this->view = $view;
        $this->config = $config;
    }

    /**
     * Private caching time one year
     */
    const PRIVATE_MAX_AGE_CACHE = 31536000;

    /**
     * Retrieve url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route, array $params = array())
    {
        return $this->_getUrl($route, $params);
    }

    /**
     * Get handles applied for current page
     *
     * @return array
     */
    public function getActualHandles()
    {
        $handlesPage = $this->view->getLayout()->getUpdate()->getHandles();
        $handlesConfig = $this->config->getPageLayoutHandles();
        $appliedHandles = array_intersect($handlesPage, $handlesConfig);
        $resultHandles = array_merge(['default'], array_values($appliedHandles));

        return $resultHandles;
    }
}
