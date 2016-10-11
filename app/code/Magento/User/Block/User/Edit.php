<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Block\User;

/**
 * User edit page
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
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

            $deleteConfirmMsg = __("Are you sure you want to revoke the user\'s tokens?");
            $this->addButton(
                'invalidate',
                [
                    'label' => __('Force Sign-In'),
                    'class' => 'invalidate-token',
                    'onclick' => "deleteConfirm('" . $deleteConfirmMsg . "', '" . $this->getInvalidateUrl() . "')",
                ]
            );
        }
    }

    /**
     * Returns message that is displayed for admin when he deleted user from the system.
     * To see this message admin must do the following:
     * - open user for edition;
     * - fill current password in section "Current User Identity Verification";
     * - click "Delete User" at top left part of the page;
     *
     * @return \Magento\Framework\Phrase
     */
    public function getDeleteMessage()
    {
        return __('Are you sure you want to do this?');
    }

    /**
     * Returns url that for user deletion.
     * The following action is executed if admin navigates to this url
     * Magento\User\Controller\Adminhtml\User\Delete::execute
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('adminhtml/*/delete');
    }

    /**
     * Method is used to get id of user that admin edits.
     * It can be used to determine either admin opens page for creation or edition of already created user
     *
     * @return int
     */
    public function getObjectId()
    {
        return (int)$this->getRequest()->getParam($this->_objectId);
    }

    /**
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
        return $this->getUrl('adminhtml/*/invalidatetoken', ['_current' => true]);
    }
}
