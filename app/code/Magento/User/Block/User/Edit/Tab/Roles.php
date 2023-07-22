<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block\User\Edit\Tab;

use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\User\Model\User;

/**
 * Roles grid
 *
 * @api
 * @since 100.0.2
 */
class Roles extends Extended
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    protected $_userRolesFactory;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param EncoderInterface $jsonEncoder
     * @param CollectionFactory $userRolesFactory
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        EncoderInterface $jsonEncoder,
        CollectionFactory $userRolesFactory,
        Registry $coreRegistry,
        array $data = []
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
     * Adds column filter to collection
     *
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
                $this->getCollection()->addFieldToFilter('role_id', ['in' => $userRoles]);
            } else {
                if ($userRoles) {
                    $this->getCollection()->addFieldToFilter('role_id', ['nin' => $userRoles]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepares collection
     *
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
     * Prepares columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'assigned_user_role',
            [
                'header_css_class' => 'data-grid-actions-cell',
                'header' => __('Assigned'),
                'type' => 'radio',
                'html_name' => 'roles[]',
                'values' => $this->getSelectedRoles(),
                'align' => 'center',
                'index' => 'role_id'
            ]
        );

        $this->addColumn('role_name', ['header' => __('Role'), 'index' => 'role_name']);

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        $userPermissions = $this->_coreRegistry->registry('permissions_user');
        return $this->getUrl('*/*/rolesGrid', ['user_id' => $userPermissions->getUserId()]);
    }

    /**
     * Gets selected roles
     *
     * @param bool $json
     * @return array|string
     */
    public function getSelectedRoles($json = false)
    {
        $userRoles = $this->getRequest()->getParam('user_roles');
        if ($userRoles) {
            if ($json) {
                $result = json_decode($userRoles);
                return $result ? $this->_jsonEncoder->encode($result) : '{}';
            }
            return $this->escapeJs($this->escapeHtml($userRoles));
        }
        /* @var $user User */
        $user = $this->_coreRegistry->registry('permissions_user');
        //checking if we have this data and we
        //don't need load it through resource model
        if ($user->hasData('roles')) {
            $userRoles = $user->getData('roles');
        } else {
            $userRoles = $user->getRoles();
        }

        if ($json) {
            $jsonRoles = [];
            foreach ($userRoles as $roleId) {
                $jsonRoles[$roleId] = 0;
            }
            return $this->_jsonEncoder->encode((object)$jsonRoles);
        } else {
            return $userRoles;
        }
    }
}
