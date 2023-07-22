<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block;

use Magento\Backend\Block\Widget\Container as WidgetContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Container;
use Magento\User\Model\ResourceModel\User as ResourceUser;

/**
 * User block
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class User extends Container
{
    /**
     * @var ResourceUser
     */
    protected $_resourceModel;

    /**
     * @param Context $context
     * @param ResourceUser $resourceModel
     * @param array $data
     */
    public function __construct(
        Context $context,
        ResourceUser $resourceModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_resourceModel = $resourceModel;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addData(
            [
                WidgetContainer::PARAM_CONTROLLER => 'user',
                Container::PARAM_BLOCK_GROUP => 'Magento_User',
                Container::PARAM_BUTTON_NEW => __('Add New User'),
                WidgetContainer::PARAM_HEADER_TEXT => __('Users'),
            ]
        );
        parent::_construct();
        $this->_addNewButton();
    }
}
