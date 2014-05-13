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
 * Newsletter queue grid block action item renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml\Queue\Grid\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * Renders column
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        $actions = array();

        if ($row->getQueueStatus() == \Magento\Newsletter\Model\Queue::STATUS_NEVER) {
            if (!$row->getQueueStartAt() && $row->getSubscribersTotal()) {
                $actions[] = array(
                    'url' => $this->getUrl('*/*/start', array('id' => $row->getId())),
                    'caption' => __('Start')
                );
            }
        } elseif ($row->getQueueStatus()==\Magento\Newsletter\Model\Queue::STATUS_SENDING) {
            $actions[] = array(
                'url' => $this->getUrl('*/*/pause', array('id' => $row->getId())),
                'caption' => __('Pause')
            );

            $actions[] = array(
                'url' => $this->getUrl('*/*/cancel', array('id' => $row->getId())),
                'confirm' => __('Do you really want to cancel the queue?'),
                'caption' => __('Cancel')
            );
        } elseif ($row->getQueueStatus()==\Magento\Newsletter\Model\Queue::STATUS_PAUSE) {

            $actions[] = array(
                'url' => $this->getUrl('*/*/resume', array('id' => $row->getId())),
                'caption' => __('Resume')
            );
        }

        $actions[] = array(
            'url' => $this->getUrl('*/*/preview', array('id' => $row->getId())),
            'caption' => __('Preview'),
            'popup' => true
        );

        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }
}
