<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Block\User;

/**
 * User edit page
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
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
                        "', '" . $this->getInvalidateUrl() . "')",
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getObjectId()
    {
        return (int)$this->getRequest()->getParam($this->_objectId);
    }

    /**
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getValidationUrl()
    {
        return $this->getUrl('adminhtml/*/validate', ['_current' => true]);
    }

    /**
     * Return invalidate url for edit form
     *
     * @return string
     * @since 2.0.0
     */
    public function getInvalidateUrl()
    {
        return $this->getUrl('adminhtml/*/invalidatetoken', ['_current' => true]);
    }
}
