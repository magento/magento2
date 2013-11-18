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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Acl role user grid.
 */
namespace Magento\User\Block\Role\Grid;

class User extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Factory for user role model
     *
     * @var \Magento\User\Model\RoleFactory
     */
    protected $_roleFactory;

    /**
     * Construct
     *
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Url $urlModel
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\User\Model\RoleFactory $roleFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Url $urlModel,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\User\Model\RoleFactory $roleFactory,
        array $data = array()
    ) {
        parent::__construct($coreData, $context, $storeManager, $urlModel, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_roleFactory = $roleFactory;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('role_user_id');
        $this->setDefaultDir('asc');
        $this->setId('roleUserGrid');
        $this->setDefaultFilter(array('in_role_users'=>1));
        $this->setUseAjax(true);
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_role_users') {
            $inRoleIds = $this->getUsers();
            if (empty($inRoleIds)) {
                $inRoleIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('user_id', array('in'=>$inRoleIds));
            } else {
                if ($inRoleIds) {
                    $this->getCollection()->addFieldToFilter('user_id', array('nin'=>$inRoleIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $roleId = $this->getRequest()->getParam('rid');
        $this->_coreRegistry->register('RID', $roleId);
        $collection = $this->_roleFactory->create()->getUsersCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_role_users', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_role_users',
            'values'    => $this->getUsers(),
            'align'     => 'center',
            'index'     => 'user_id'
        ));

        $this->addColumn('role_user_id', array(
            'header'    =>__('User ID'),
            'width'     =>5,
            'align'     =>'left',
            'sortable'  =>true,
            'index'     =>'user_id'
        ));

        $this->addColumn('role_user_username', array(
            'header'    =>__('User Name'),
            'align'     =>'left',
            'index'     =>'username'
        ));

        $this->addColumn('role_user_firstname', array(
            'header'    =>__('First Name'),
            'align'     =>'left',
            'index'     =>'firstname'
        ));

        $this->addColumn('role_user_lastname', array(
            'header'    =>__('Last Name'),
            'align'     =>'left',
            'index'     =>'lastname'
        ));

        $this->addColumn('role_user_email', array(
            'header'    =>__('Email'),
            'width'     =>40,
            'align'     =>'left',
            'index'     =>'email'
        ));

        $this->addColumn('role_user_is_active', array(
            'header'    => __('Status'),
            'index'     => 'is_active',
            'align'     =>'left',
            'type'      => 'options',
            'options'   => array(
                '1' => __('Active'),
                '0' => __('Inactive')
            ),
        ));

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

    public function getGridUrl()
    {
        $roleId = $this->getRequest()->getParam('rid');
        return $this->getUrl('*/*/editrolegrid', array('rid' => $roleId));
    }

    public function getUsers($json=false)
    {
        if ( $this->getRequest()->getParam('in_role_user') != "" ) {
            return $this->getRequest()->getParam('in_role_user');
        }
        $roleId = ( $this->getRequest()->getParam('rid') > 0 ) ?
            $this->getRequest()->getParam('rid') :
            $this->_coreRegistry->registry('RID');
        $users = $this->_roleFactory->create()->setId($roleId)->getRoleUsers();
        if (sizeof($users) > 0) {
            if ($json) {
                $jsonUsers = array();
                foreach ($users as $usrid) {
                    $jsonUsers[$usrid] = 0;
                }
                return $this->_coreData->jsonEncode((object)$jsonUsers);
            } else {
                return array_values($users);
            }
        } else {
            if ($json) {
                return '{}';
            } else {
                return array();
            }
        }
    }
}

