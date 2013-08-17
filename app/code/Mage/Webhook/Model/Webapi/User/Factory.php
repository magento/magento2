<?php
/**
 * Creates user with proper permissions for subscription
 *
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Webapi_User_Factory
{
    /** Keys used in user context array */
    const CONTEXT_EMAIL = 'email';
    const CONTEXT_COMPANY = 'company';
    const CONTEXT_KEY = 'key';
    const CONTEXT_SECRET = 'secret';

    /** name delimiter */
    const NAME_DELIM = ' - ';

    /** @var Mage_Webapi_Model_Acl_Rule_Factory  */
    private $_ruleFactory;

    /** @var Mage_Webapi_Model_Acl_User_Factory  */
    private $_userFactory;

    /** @var Mage_Webapi_Model_Acl_Role_Factory  */
    private $_roleFactory;

    /** @var array virtual resource to resource mapping  */
    private $_topicMapping = array();

    /** @var Magento_Acl_CacheInterface  */
    protected $_cache;

    /** @var Mage_Core_Helper_Data  */
    private $_coreHelper;

    /**
     * @param Mage_Webapi_Model_Acl_Rule_Factory $ruleFactory
     * @param Mage_Webapi_Model_Acl_User_Factory $userFactory
     * @param Mage_Webapi_Model_Acl_Role_Factory $roleFactory
     * @param Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader $authorizationConfig
     * @param Mage_Webapi_Model_Acl_Cache $cache
     * @param Mage_Core_Helper_Data $coreHelper
     */
    public function __construct(
        Mage_Webapi_Model_Acl_Rule_Factory $ruleFactory,
        Mage_Webapi_Model_Acl_User_Factory $userFactory,
        Mage_Webapi_Model_Acl_Role_Factory $roleFactory,
        Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader $authorizationConfig,
        Mage_Webapi_Model_Acl_Cache $cache,
        Mage_Core_Helper_Data $coreHelper
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->_userFactory = $userFactory;
        $this->_roleFactory = $roleFactory;
        $this->_coreHelper = $coreHelper;
        $this->_cache = $cache;
        $this->_initVirtualResourceMapping($authorizationConfig);
    }

    /**
     * Creates a new user and role for the subscription associated with this Webapi.
     *
     * @param array $userContext Information needed to create a user: email, company, secret, key
     * @param array $topics Resources the user should have access to
     * @return int Webapi user id
     * @throws Exception If a new user can't be created (because of DB issues for instance)
     */
    public function createUser(array $userContext, array $topics)
    {
        $role = $this->_createWebapiRole($userContext[self::CONTEXT_EMAIL], $userContext[self::CONTEXT_COMPANY]);

        try {
            $this->_createWebapiRule($topics, $role->getId());
            $user = $this->_createWebapiUser($userContext, $role);
        } catch (Exception $e) {
            $role->delete();
            throw $e;
        }

        return $user->getId();
    }

    /**
     * Creates a new Mage_Webapi_Model_Acl_Role role with a unique name
     *
     * @param string $email
     * @param string $company
     * @return Mage_Webapi_Model_Acl_Role
     */
    protected function _createWebapiRole($email, $company)
    {
        $roleName = $this->_createRoleName($email, $company);
        $role     = $this->_roleFactory->create()->load($roleName, 'role_name');

        // Check if a role with this name already exists, we need a new role with a unique name
        if ($role->getId()) {
            $uniqString = $this->_coreHelper->uniqHash();
            $roleName   = $this->_createRoleName($email, $company, $uniqString);
        }

        $role = $this->_roleFactory->create()
            ->setRoleName($roleName)
            ->save();

        return $role;
    }

    /**
     * Creates a rule and associates it with a role
     *
     * @param array $topics
     * @param int $roleId
     * @return null
     */
    public function _createWebapiRule(array $topics, $roleId)
    {
        $resources = array();
        foreach ($topics as $topic) {
            $resources[] = $this->_topicMapping[$topic];
        }
        array_unique($resources);

        $resources = array_merge($resources, array(
            'webhook/create',
            'webhook/get',
            'webhook/update',
            'webhook/delete',
        ));

        $this->_ruleFactory->create()
            ->setRoleId($roleId)
            ->setResources($resources)
            ->saveResources();

        /* Updating the ACL cache so that new role appears there */
        $this->_cache->clean();
    }

    /**
     * Creates a webapi User in the DB
     *
     * @param array $userContext
     * @param Mage_Webapi_Model_Acl_Role $role
     * @return Mage_Core_Model_Abstract
     */
    protected function _createWebapiUser(array $userContext, $role)
    {
        $user = $this->_userFactory->create()
            ->setRoleId($role->getId())
            ->setApiKey($userContext[self::CONTEXT_KEY])
            ->setSecret($userContext[self::CONTEXT_SECRET])
            ->setCompanyName($userContext[self::CONTEXT_COMPANY])
            ->setContactEmail($userContext[self::CONTEXT_EMAIL])
            ->save();
        return $user;
    }

    /**
     * Create unique role name
     *
     * @param string $email
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    protected function _createRoleName($email, $prefix = null, $suffix = null)
    {
        $result = '';
        if ($prefix) {
            $result = $prefix . self::NAME_DELIM;
        }

        $result .= $email;

        if ($suffix) {
            $result .= self::NAME_DELIM . $suffix;
        }
        return $result;
    }

    /**
     * Initialize our virtual resource to merchant visible resource mapping array.
     *
     * @param Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader $authorizationConfig
     */
    protected function _initVirtualResourceMapping(
        Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader $authorizationConfig
    ) {
        $virtualResources = $authorizationConfig->getAclVirtualResources();
        /** @var DOMElement $resourceDom */
        foreach ($virtualResources as $resourceDom) {
            $virtualResource = $resourceDom->getAttribute('id');
            $parentResource = $resourceDom->getAttribute('parent');
            $this->_topicMapping[$virtualResource] = $parentResource;
        }
    }
}
