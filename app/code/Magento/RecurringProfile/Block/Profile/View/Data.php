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

namespace Magento\RecurringProfile\Block\Profile\View;

/**
 * Recurring profile view data
 */
class Data extends \Magento\RecurringProfile\Block\Profile\View
{
    /**
     * Prepare profile data
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->addData(array(
            'reference_id' => $this->_recurringProfile->getReferenceId(),
            'can_cancel'   => $this->_recurringProfile->canCancel(),
            'cancel_url'   => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_recurringProfile->getId(),
                    'action' => 'cancel'
                )
            ),
            'can_suspend'  => $this->_recurringProfile->canSuspend(),
            'suspend_url'  => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_recurringProfile->getId(),
                    'action' => 'suspend'
                )
            ),
            'can_activate' => $this->_recurringProfile->canActivate(),
            'activate_url' => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_recurringProfile->getId(),
                    'action' => 'activate'
                )
            ),
            'can_update'   => $this->_recurringProfile->canFetchUpdate(),
            'update_url'   => $this->getUrl(
                '*/*/updateProfile',
                array(
                    'profile' => $this->_recurringProfile->getId()
                )
            ),
            'back_url'     => $this->getUrl('*/*/'),
            'confirmation_message' => __('Are you sure you want to do this?'),
        ));
    }
}
