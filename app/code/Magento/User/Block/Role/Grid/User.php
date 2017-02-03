<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block\Role\Grid;

use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Acl role user grid.
 */
class User extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Factory for user role model
     *
     * @var \Magento\Authorization\Model\RoleFactory
     */
    protected $_roleFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\User\Model\ResourceModel\Role\User\CollectionFactory
     */
    protected $_userRolesFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\User\Model\ResourceModel\Role\User\CollectionFactory $userRolesFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\User\Model\ResourceModel\Role\User\CollectionFactory $userRolesFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        $this->_roleFactory = $roleFactory;
        $this->_userRolesFactory = $userRolesFactory;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('role_user_id');
        $this->setDefaultDir('asc');
        $this->setId('roleUserGrid');
        $this->setUseAjax(true);
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_role_users') {
            $inRoleIds = $this->getUsers();
            if (empty($inRoleIds)) {
                $inRoleIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('user_id', ['in' => $inRoleIds]);
            } else {
                if ($inRoleIds) {
                    $this->getCollection()->addFieldToFilter('user_id', ['nin' => $inRoleIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $roleId = $this->getRequest()->getParam('rid');
        $this->_coreRegistry->register('RID', $roleId);
        $collection = $this->_userRolesFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_role_users',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_role_users',
                'values' => $this->getUsers(),
                'align' => 'center',
                'index' => 'user_id'
            ]
        );

        $this->addColumn(
            'role_user_id',
            ['header' => __('User ID'), 'width' => 5, 'align' => 'left', 'sortable' => true, 'index' => 'user_id']
        );

        $this->addColumn(
            'role_user_username',
            ['header' => __('User Name'), 'align' => 'left', 'index' => 'username']
        );

        $this->addColumn(
            'role_user_firstname',
            ['header' => __('First Name'), 'align' => 'left', 'index' => 'firstname']
        );

        $this->addColumn(
            'role_user_lastname',
            ['header' => __('Last Name'), 'align' => 'left', 'index' => 'lastname']
        );

        $this->addColumn(
            'role_user_email',
            ['header' => __('Email'), 'width' => 40, 'align' => 'left', 'index' => 'email']
        );

        $this->addColumn(
            'role_user_is_active',
            [
                'header' => __('Status'),
                'index' => 'is_active',
                'align' => 'left',
                'type' => 'options',
                'options' => ['1' => __('Active'), '0' => __('Inactive')]
            ]
        );

        /*
        $this->addColumn('grid_actions',
            array(
                'header'=>__('Actions'),
                'width'=>5,
                'sortable'=>false,
                'filter'    =>false,
                'type' => 'action',
                'actions'   => array(
                                    array(
                                        'caption' => __('Remove'),
                                        'onClick' => 'role.deleteFromRole($role_id);'
                                    )
                                )
            )
        );
        */

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $roleId = $this->getRequest()->getParam('rid');
        return $this->getUrl('*/*/editrolegrid', ['rid' => $roleId]);
    }

    /**
     * @param bool $json
     * @return string|array
     */
    public function getUsers($json = false)
    {
        if ($this->getRequest()->getParam('in_role_user') != "") {
            return $this->getRequest()->getParam('in_role_user');
        }
        $roleId = $this->getRequest()->getParam(
            'rid'
        ) > 0 ? $this->getRequest()->getParam(
            'rid'
        ) : $this->_coreRegistry->registry(
            'RID'
        );
        $users = $this->_roleFactory->create()->setId($roleId)->getRoleUsers();
        if (sizeof($users) > 0) {
            if ($json) {
                $jsonUsers = [];
                foreach ($users as $usrid) {
                    $jsonUsers[$usrid] = 0;
                }
                return $this->_jsonEncoder->encode((object)$jsonUsers);
            } else {
                return array_values($users);
            }
        } else {
            if ($json) {
                return '{}';
            } else {
                return [];
            }
        }
    }
}
