<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

        $objId = $this->getRequest()->getParam($this->_objectId);

        if (!empty($objId)) {
            $this->addButton(
                'delete',
                [
                    'label' => __('Delete User'),
                    'class' => 'delete',
                    'onclick' => sprintf(
                        'deleteConfirm("%s", "%s", %s)',
                        __('Are you sure you want to do this?'),
                        $this->getUrl('adminhtml/*/delete'),
                        json_encode(['data' => ['user_id' => $objId]])
                    ),
                ]
            );

            $deleteConfirmMsg = __("Are you sure you want to revoke the user\'s tokens?");
            $this->addButton(
                'invalidate',
                [
                    'label' => __('Force Sign-In'),
                    'class' => 'invalidate-token',
                    'onclick' => 'deleteConfirm(\'' . $deleteConfirmMsg . '\', \'' . $this->getInvalidateUrl() . '\')',
                ]
            );
        }
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
