<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Setup;

/**
 * Resource Setup Model
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class AuthorizationFactory
{
    /**
     * Role model factory
     *
     * @var \Magento\Authorization\Model\RoleFactory
     * @since 2.0.0
     */
    protected $_roleCollectionFactory;

    /**
     * Factory for rules model
     *
     * @var \Magento\Authorization\Model\RulesFactory
     * @since 2.0.0
     */
    protected $_rulesCollectionFactory;

    /**
     * Role model factory
     *
     * @var \Magento\Authorization\Model\RoleFactory
     * @since 2.0.0
     */
    protected $_roleFactory;

    /**
     * Rules model factory
     *
     * @var \Magento\Authorization\Model\RulesFactory
     * @since 2.0.0
     */
    protected $_rulesFactory;

    /**
     * Init
     *
     * @param \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory
     * @param \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory
     * @param \Magento\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\Authorization\Model\RulesFactory $rulesFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory,
        \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Authorization\Model\RulesFactory $rulesFactory
    ) {
        $this->_roleCollectionFactory = $roleCollectionFactory;
        $this->_rulesCollectionFactory = $rulesCollectionFactory;
        $this->_roleFactory = $roleFactory;
        $this->_rulesFactory = $rulesFactory;
    }

    /**
     * Creates role collection
     *
     * @return \Magento\Authorization\Model\ResourceModel\Role\Collection
     * @since 2.0.0
     */
    public function createRoleCollection()
    {
        return $this->_roleCollectionFactory->create();
    }

    /**
     * Creates rules collection
     *
     * @return \Magento\Authorization\Model\ResourceModel\Rules\Collection
     * @since 2.0.0
     */
    public function createRulesCollection()
    {
        return $this->_rulesCollectionFactory->create();
    }

    /**
     * Creates role model
     *
     * @return \Magento\Authorization\Model\Role
     * @since 2.0.0
     */
    public function createRole()
    {
        return $this->_roleFactory->create();
    }

    /**
     * Creates rules model
     *
     * @return \Magento\Authorization\Model\Rules
     * @since 2.0.0
     */
    public function createRules()
    {
        return $this->_rulesFactory->create();
    }
}
