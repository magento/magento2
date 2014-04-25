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

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;

/**
 * Pricing render's layout model
 */
class Layout
{
    /**
     * Layout Interface
     *
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * Constructor
     *
     * @param LayoutFactory $layoutFactory
     * @param LayoutInterface $generalLayout
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        \Magento\Framework\View\LayoutInterface $generalLayout
    ) {
        $this->layout = $layoutFactory->create(['cacheable' => $generalLayout->isCacheable()]);
    }

    /**
     * Add handle(s) to layout
     *
     * @param string|string[] $handle
     * @return void
     */
    public function addHandle($handle)
    {
        $this->layout->getUpdate()->addHandle($handle);
    }

    /**
     * Load layout
     *
     * @return void
     */
    public function loadLayout()
    {
        $this->layout->getUpdate()->load();
        $this->layout->generateXml();
        $this->layout->generateElements();
    }

    /**
     * Obtain block object
     *
     * @param string $name
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function getBlock($name)
    {
        return $this->layout->getBlock($name);
    }
}
