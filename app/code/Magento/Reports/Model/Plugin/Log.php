<?php
/**
 * Plugin for \Magento\Log\Model\Resource\Log model
 *
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
namespace Magento\Reports\Model\Plugin;

class Log
{
    /**
     * @var \Magento\Reports\Model\Event
     */
    protected $_reportEvent;

    /**
     * @var \Magento\Reports\Model\Product\Index\Compared
     */
    protected $_comparedProductIdx;

    /**
     * @var \Magento\Reports\Model\Product\Index\Viewed
     */
    protected $_viewedProductIdx;

    /**
     * @param \Magento\Reports\Model\Event $reportEvent
     * @param \Magento\Reports\Model\Product\Index\Compared $comparedProductIdx
     * @param \Magento\Reports\Model\Product\Index\Viewed $viewedProductIdx
     */
    public function __construct(
        \Magento\Reports\Model\Event $reportEvent,
        \Magento\Reports\Model\Product\Index\Compared $comparedProductIdx,
        \Magento\Reports\Model\Product\Index\Viewed $viewedProductIdx
    ) {
        $this->_reportEvent = $reportEvent;
        $this->_comparedProductIdx = $comparedProductIdx;
        $this->_viewedProductIdx = $viewedProductIdx;
    }

    /**
     * Clean events by old visitors after plugin for clean method
     *
     * @param \Magento\Log\Model\Resource\Log $subject
     * @param \Magento\Log\Model\Resource\Log $logResourceModel
     *
     * @return \Magento\Log\Model\Resource\Log
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @see Global Log Clean Settings
     */
    public function afterClean(\Magento\Log\Model\Resource\Log $subject, $logResourceModel)
    {
        $this->_reportEvent->clean();
        $this->_comparedProductIdx->clean();
        $this->_viewedProductIdx->clean();
        return $logResourceModel;
    }
}
