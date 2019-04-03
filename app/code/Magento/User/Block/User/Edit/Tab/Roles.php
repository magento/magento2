<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block\User\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Roles grid
 */
class Roles extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory
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
     * @param \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory,
        \Magento\Framework\Registry $coreRegistry,
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
     * Gets grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        $userPermissions = $this->_coreRegistry->registry('permissions_user');
        return $this->getUrl('*/*/rolesGrid', ['user_id' => $userPermissions->getUserId()]);
    }

    /**
     * Get selected roles
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
            return $this->_escaper->escapeJs($this->escapeHtml($userRoles));
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
            $jsonRoles = [];
            foreach ($uRoles as $urid) {
                $jsonRoles[$urid] = 0;
            }
            return $this->_jsonEncoder->encode((object)$jsonRoles);
        } else {
            return $uRoles;
        }
    }
}
