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
namespace Magento\User\Block\User\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Roles extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Authorization\Model\Resource\Role\CollectionFactory
     */
    protected $_userRolesFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Authorization\Model\Resource\Role\CollectionFactory $userRolesFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Authorization\Model\Resource\Role\CollectionFactory $userRolesFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_userRolesFactory = $userRolesFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('permissionsUserRolesGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('asc');
        $this->setTitle(__('User Roles Information'));
        $this->setUseAjax(true);
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'assigned_user_role') {
            $userRoles = $this->getSelectedRoles();
            if (empty($userRoles)) {
                $userRoles = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('role_id', array('in' => $userRoles));
            } else {
                if ($userRoles) {
                    $this->getCollection()->addFieldToFilter('role_id', array('nin' => $userRoles));
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
        $collection = $this->_userRolesFactory->create();
        $collection->setRolesFilter();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'assigned_user_role',
            array(
                'header_css_class' => 'a-center',
                'header' => __('Assigned'),
                'type' => 'radio',
                'html_name' => 'roles[]',
                'values' => $this->getSelectedRoles(),
                'align' => 'center',
                'index' => 'role_id'
            )
        );

        $this->addColumn('role_name', array('header' => __('Role'), 'index' => 'role_name'));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $userPermissions = $this->_coreRegistry->registry('permissions_user');
        return $this->getUrl('*/*/rolesGrid', array('user_id' => $userPermissions->getUserId()));
    }

    /**
     * @param bool $json
     * @return array|string
     */
    public function getSelectedRoles($json = false)
    {
        if ($this->getRequest()->getParam('user_roles') != "") {
            return $this->getRequest()->getParam('user_roles');
        }
        /* @var $user \Magento\User\Model\User */
        $user = $this->_coreRegistry->registry('permissions_user');
        //checking if we have this data and we
        //don't need load it through resource model
        if ($user->hasData('roles')) {
            $uRoles = $user->getData('roles');
        } else {
            $uRoles = $user->getRoles();
        }

        if ($json) {
            $jsonRoles = array();
            foreach ($uRoles as $urid) {
                $jsonRoles[$urid] = 0;
            }
            return $this->_jsonEncoder->encode((object)$jsonRoles);
        } else {
            return $uRoles;
        }
    }
}
