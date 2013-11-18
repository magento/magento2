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
 * @package     Magento_Page
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Page layout helper
 *
 * @category   Magento
 * @package    Magento_Page
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Page\Helper;

class Layout extends \Magento\Core\Helper\AbstractHelper
{
    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Page\Model\Config
     */
    protected $_config;

    /**
     * @param \Magento\Page\Model\Config $config
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Core\Helper\Context $context
     */
    public function __construct(
        \Magento\Page\Model\Config $config,
        \Magento\View\LayoutInterface $layout,
        \Magento\Core\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->_layout = $layout;
        $this->_config = $config;
    }

    /**
     * Apply page layout handle
     *
     * @param string $pageLayout
     * @return \Magento\Page\Helper\Layout
     */
    public function applyHandle($pageLayout)
    {
        $pageLayout = $this->_config->getPageLayout($pageLayout);

        if (!$pageLayout) {
            return $this;
        }

        $this->_layout->getUpdate()
            ->addHandle($pageLayout->getLayoutHandle());

        return $this;
    }

    /**
     * Apply page layout template
     * (for old design packages)
     *
     * @param string $pageLayout
     * @return \Magento\Page\Helper\Layout
     */
    public function applyTemplate($pageLayout = null)
    {
        if ($pageLayout === null) {
            $pageLayout = $this->getCurrentPageLayout();
        } else {
            $pageLayout = $this->_config->getPageLayout($pageLayout);
        }

        if (!$pageLayout) {
            return $this;
        }

        if ($this->_layout->getBlock('root') &&
            !$this->_layout->getBlock('root')->getIsHandle()) {
                // If not applied handle
                $this->_layout->getBlock('root')
                    ->setTemplate($pageLayout->getTemplate());
        }

        return $this;
    }

    /**
     * Retrieve current applied page layout
     *
     * @return \Magento\Object|boolean
     */
    public function getCurrentPageLayout()
    {
        if ($this->_layout->getBlock('root') &&
            $this->_layout->getBlock('root')->getLayoutCode()) {
            return $this->_config->getPageLayout($this->_layout->getBlock('root')->getLayoutCode());
        }

        // All loaded handles
        $handles = $this->_layout->getUpdate()->getHandles();
        // Handles used in page layouts
        $pageLayoutHandles = $this->_config->getPageLayoutHandles();
        // Applied page layout handles
        $appliedHandles = array_intersect($handles, $pageLayoutHandles);

        if (empty($appliedHandles)) {
            return false;
        }

        $currentHandle = array_pop($appliedHandles);

        $layoutCode = array_search($currentHandle, $pageLayoutHandles, true);

        return $this->_config->getPageLayout($layoutCode);
    }
}
