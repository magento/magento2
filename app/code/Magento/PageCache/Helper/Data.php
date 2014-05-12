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

/**
 * Page cache data helper
 *
 */
namespace Magento\PageCache\Helper;

/**
 * Helper for Page Cache module
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Private caching time one year
     */
    const PRIVATE_MAX_AGE_CACHE = 31536000;

    /**
     * @var \Magento\Framework\App\View
     */
    protected $view;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\View $view
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\View $view
    ) {
        parent::__construct($context);
        $this->view = $view;
    }

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
        return $this->view->getLayout()->getUpdate()->getHandles();
    }
}
