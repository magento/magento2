<?php
/**
 * Renders html code for subscription grid items
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Block\Adminhtml\Subscription\Grid\Renderer;

class Action
    extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render a given html for the subscription grid
     *
     * @param \Magento\Object $row
     * @return string The rendered html code for a given row
     */
    public function render(\Magento\Object $row)
    {
        if (!($row instanceof \Magento\Webhook\Model\Subscription)) {
            return '';
        }

        switch ($row->getStatus()) {
            case \Magento\Webhook\Model\Subscription::STATUS_ACTIVE :
                return '<a href="' . $this->getUrl('*/webhook_subscription/revoke', array('id' => $row->getId()))
                    . '">' . __('Revoke') . '</a>';
            case \Magento\Webhook\Model\Subscription::STATUS_REVOKED :
                return '<a href="' . $this->getUrl('*/webhook_subscription/activate', array('id' => $row->getId()))
                    . '">' . __('Activate') . '</a>';
            case  \Magento\Webhook\Model\Subscription::STATUS_INACTIVE :
                $url = $this->getUrl('*/webhook_registration/activate', array('id' => $row->getId()));
                return '<a href="#" onclick="activateSubscription(\''. $url .'\'); return false;">'
                    . __('Activate') . '</a>';
            default :
                return '';
        }
    }
}
