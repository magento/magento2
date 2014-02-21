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

namespace Magento\View;

class DesignLoader
{
    /**
     * Request
     *
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * Application
     *
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * Layout
     *
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Constructor
     *
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Core\Model\App $app
     * @param \Magento\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\Core\Model\App $app,
        \Magento\View\LayoutInterface $layout
    ) {
        $this->_request = $request;
        $this->_app = $app;
        $this->_layout = $layout;
    }

    /**
     * Load design
     *
     * @return void
     */
    public function load()
    {
        $area = $this->_app->getArea($this->_layout->getArea());
        $area->load(\Magento\Core\Model\App\Area::PART_DESIGN);
        $area->load(\Magento\Core\Model\App\Area::PART_TRANSLATE);
        $area->detectDesign($this->_request);
    }
}
