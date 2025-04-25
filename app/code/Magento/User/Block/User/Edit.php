<?php
/************************************************************************
 *
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\User\Block\User;

/**
 * User edit page
 *
 * @api
 * @since 100.0.2
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'user_id';
        $this->_controller = 'user';
        $this->_blockGroup = 'Magento_User';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save User'));
        $this->buttonList->remove('delete');

        $objId = (int)$this->getRequest()->getParam($this->_objectId);

        if (!empty($objId)) {
            $this->addButton(
                'delete',
                [
                    'label' => __('Delete User'),
                    'class' => 'delete',
                    'data_attribute' => [
                        'role' => 'delete-user'
                    ]
                ]
            );

            $deleteConfirmMsg = __("Are you sure you want to revoke the user's tokens?");
            $this->addButton(
                'invalidate',
                [
                    'label' => __('Force Sign-In'),
                    'class' => 'invalidate-token',
                    'onclick' => "deleteConfirm('" . $this->escapeJs($this->escapeHtml($deleteConfirmMsg)) .
                        "', '" . $this->getInvalidateUrl() . "', {data:{{$this->_objectId}:{$objId}}})",
                ]
            );
        }
    }

    /**
     * Returns message that is displayed for admin when he deletes user from the system.
     * To see this message admin must do the following:
     * - open user's account for editing;
     * - type current user's password in the "Current User Identity Verification" field
     * - click "Delete User" at top left part of the page;
     *
     * @return \Magento\Framework\Phrase
     * @since 101.0.0
     */
    public function getDeleteMessage()
    {
        return __('Are you sure you want to do this?');
    }

    /**
     * Returns the URL that is used for user deletion.
     * The following Action is executed if admin navigates to returned url
     * Magento\User\Controller\Adminhtml\User\Delete
     *
     * @return string
     * @since 101.0.0
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('adminhtml/*/delete');
    }

    /**
     * This method is used to get the ID of the user who's account the Admin is editing.
     * It can be used to determine the reason Admin opens the page:
     * to create a new user account OR to edit the previously created user account
     *
     * @return int
     * @since 101.0.0
     */
    public function getObjectId()
    {
        return (int)$this->getRequest()->getParam($this->_objectId);
    }

    /**
     * Get text to be used in the header
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('permissions_user')->getId()) {
            $username = $this->escapeHtml($this->_coreRegistry->registry('permissions_user')->getUsername());
            return __("Edit User '%1'", $username);
        } else {
            return __('New User');
        }
    }

    /**
     * Return validation url for edit form
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('adminhtml/*/validate', ['_current' => true]);
    }

    /**
     * Return invalidate url for edit form
     *
     * @return string
     */
    public function getInvalidateUrl()
    {
        return $this->getUrl('adminhtml/*/invalidatetoken');
    }
}
