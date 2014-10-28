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
namespace Magento\Framework\App;

interface ViewInterface
{
    /**
     * Load layout updates
     *
     * @return ViewInterface
     */
    public function loadLayoutUpdates();

    /**
     * Rendering layout
     *
     * @param   string $output
     * @return  ViewInterface
     */
    public function renderLayout($output = '');

    /**
     * Retrieve the default layout handle name for the current action
     *
     * @return string
     */
    public function getDefaultLayoutHandle();

    /**
     * Load layout by handles(s)
     *
     * @param   string|null|bool $handles
     * @param   bool $generateBlocks
     * @param   bool $generateXml
     * @param   bool $addActionHandles
     * @return  ViewInterface
     * @throws  \RuntimeException
     */
    public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true, $addActionHandles = true);

    /**
     * Generate layout xml
     *
     * @return ViewInterface
     */
    public function generateLayoutXml();

    /**
     * Add layout updates handles associated with the action page
     *
     * @param array $parameters page parameters
     * @param string $defaultHandle
     * @return bool
     */
    public function addPageLayoutHandles(array $parameters = array(), $defaultHandle = null);

    /**
     * Generate layout blocks
     *
     * @return ViewInterface
     */
    public function generateLayoutBlocks();

    /**
     * Retrieve current page object
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function getPage();

    /**
     * Retrieve current layout object
     *
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function getLayout();

    /**
     * Add layout handle by full controller action name
     *
     * @return ViewInterface
     */
    public function addActionLayoutHandles();

    /**
     * Set isLayoutLoaded flag
     *
     * @param bool $value
     * @return void
     */
    public function setIsLayoutLoaded($value);

    /**
     * Returns is layout loaded
     *
     * @return bool
     */
    public function isLayoutLoaded();
}
