<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block;

/**
 * User block
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class User extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var \Magento\User\Model\ResourceModel\User
     * @since 2.0.0
     */
    protected $_resourceModel;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\User\Model\ResourceModel\User $resourceModel
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\User\Model\ResourceModel\User $resourceModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_resourceModel = $resourceModel;
    }

    /**
     * Class constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->addData(
            [
                \Magento\Backend\Block\Widget\Container::PARAM_CONTROLLER => 'user',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BLOCK_GROUP => 'Magento_User',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BUTTON_NEW => __('Add New User'),
                \Magento\Backend\Block\Widget\Container::PARAM_HEADER_TEXT => __('Users'),
            ]
        );
        parent::_construct();
        $this->_addNewButton();
    }
}
