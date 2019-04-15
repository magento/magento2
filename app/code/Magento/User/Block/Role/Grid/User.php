<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var bool|array
     */
    protected $restoredUsersFormData;

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
     * Adds column filter to collection
     *
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
     * Prepares collection
     *
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
     * Prepares columns
     *
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

        return parent::_prepareColumns();
    }

    /**
     * Gets grid url
     *
     * @return string
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function getGridUrl()
    {
        $roleId =  $this->escapeHtml($this->getRequest()->getParam('rid'));
        return $this->getUrl('*/*/editrolegrid', ['rid' => $roleId]);
    }

    /**
     * Gets users
     *
     * @param bool $json
     * @return string|array
     */
    public function getUsers($json = false)
    {
        $inRoleUser = $this->getRequest()->getParam('in_role_user');
        if ($inRoleUser) {
            if ($json) {
                return $this->getJSONString($inRoleUser);
            }
            return $this->_escaper->escapeJs($this->escapeHtml($inRoleUser));
        }
        $roleId = $this->getRoleId();
        $users = $this->getUsersFormData();
        if (false === $users) {
            $users = $this->_roleFactory->create()->setId($roleId)->getRoleUsers();
        }
        if (!empty($users)) {
            if ($json) {
                $jsonUsers = [];
                foreach ($users as $userid) {
                    $jsonUsers[$userid] = 0;
                }
                return $this->_jsonEncoder->encode((object)$jsonUsers);
            }
            return array_values($users);
        }
        if ($json) {
            return '{}';
        }
        return [];
    }

    /**
     * Get Form Data if exist
     *
     * @return array|bool
     */
    protected function getUsersFormData()
    {
        if (false !== $this->restoredUsersFormData && null === $this->restoredUsersFormData) {
            $this->restoredUsersFormData = $this->restoreUsersFormData();
        }

        return $this->restoredUsersFormData;
    }

    /**
     * Restore Users Form Data from the registry
     *
     * @return array|bool
     * @SuppressWarnings(PHPMD.DiscouragedFunctionsSniff)
     */
    protected function restoreUsersFormData()
    {
        $sessionData = $this->_coreRegistry->registry(
            \Magento\User\Controller\Adminhtml\User\Role\SaveRole::IN_ROLE_USER_FORM_DATA_SESSION_KEY
        );
        if (null !== $sessionData) {
            parse_str($sessionData, $sessionData);
            return array_keys($sessionData);
        }

        return false;
    }

    /**
     * Gets role ID
     *
     * @return string
     */
    private function getRoleId()
    {
        $roleId = $this->getRequest()->getParam('rid');
        if ($roleId <= 0) {
            $roleId = $this->_coreRegistry->registry('RID');
        }
        return $roleId;
    }

    /**
     * Gets JSON string
     *
     * @param string $input
     * @return string
     */
    private function getJSONString($input)
    {
        $output = json_decode($input);
        return $output ? $this->_jsonEncoder->encode($output) : '{}';
    }
}
