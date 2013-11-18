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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Block\Recurring\Profile\View;

/**
 * Recurring profile view data
 */
class Data extends \Magento\Sales\Block\Recurring\Profile\View
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
            'reference_id' => $this->_profile->getReferenceId(),
            'can_cancel'   => $this->_profile->canCancel(),
            'cancel_url'   => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_profile->getId(),
                    'action' => 'cancel'
                )
            ),
            'can_suspend'  => $this->_profile->canSuspend(),
            'suspend_url'  => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_profile->getId(),
                    'action' => 'suspend'
                )
            ),
            'can_activate' => $this->_profile->canActivate(),
            'activate_url' => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_profile->getId(),
                    'action' => 'activate'
                )
            ),
            'can_update'   => $this->_profile->canFetchUpdate(),
            'update_url'   => $this->getUrl(
                '*/*/updateProfile',
                array(
                    'profile' => $this->_profile->getId()
                )
            ),
            'back_url'     => $this->getUrl('*/*/'),
            'confirmation_message' => __('Are you sure you want to do this?'),
        ));
    }
}
