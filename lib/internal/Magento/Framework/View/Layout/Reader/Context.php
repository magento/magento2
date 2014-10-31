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
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Page;

class Context
{
    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure
     */
    protected $scheduledStructure;

    /**
     * @var \Magento\Framework\View\Page\Config\Structure
     */
    protected $pageConfigStructure;

    /**
     * Constructor
     *
     * @param Layout\ScheduledStructure $scheduledStructure
     * @param \Magento\Framework\View\Page\Config\Structure $pageConfigStructure
     */
    public function __construct(
        Layout\ScheduledStructure $scheduledStructure,
        Page\Config\Structure $pageConfigStructure
    ) {
        $this->scheduledStructure = $scheduledStructure;
        $this->pageConfigStructure = $pageConfigStructure;
    }

    /**
     * @return \Magento\Framework\View\Layout\ScheduledStructure
     */
    public function getScheduledStructure()
    {
        return $this->scheduledStructure;
    }

    /**
     * @return \Magento\Framework\View\Page\Config\Structure
     */
    public function getPageConfigStructure()
    {
        return $this->pageConfigStructure;
    }
}
