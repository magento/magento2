<?php
/**
 * Abstraction of ACL Resource Loader
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Mage_Core_Model_Acl_Loader_Resource_ResourceAbstract implements Magento_Acl_Loader
{
    /**
     * Acl config
     *
     * @var Mage_Core_Model_Acl_Config_ConfigInterface
     */
    protected $_config;

    /**
     * Application object factory
     *
     * @var Magento_Acl_ResourceFactory
     */
    protected $_resourceFactory;

    /**
     * @param Mage_Core_Model_Acl_Config_ConfigInterface $configuration
     * @param Magento_Acl_ResourceFactory $resourceFactory
     */
    public function __construct(Mage_Core_Model_Acl_Config_ConfigInterface $configuration,
        Magento_Acl_ResourceFactory $resourceFactory
    ) {
        $this->_config = $configuration;
        $this->_resourceFactory = $resourceFactory;
    }

    /**
     * Populate ACL with resources from external storage
     *
     * @param Magento_Acl $acl
     * @throws Mage_Core_Exception
     */
    public function populateAcl(Magento_Acl $acl)
    {
        $this->_addResourceTree($acl, $this->_config->getAclResources(), null);
    }

    /**
     * Add list of nodes and their children to acl
     *
     * @param Magento_Acl $acl
     * @param DOMNodeList $resources
     * @param Magento_Acl_Resource $parent
     */
    protected function _addResourceTree(Magento_Acl $acl, DOMNodeList $resources, Magento_Acl_Resource $parent = null)
    {
        /** @var $resourceConfig DOMElement */
        foreach ($resources as $resourceConfig) {
            if (!($resourceConfig instanceof DOMElement)) {
                continue;
            }
            /** @var $resource Magento_Acl_Resource */
            $resource = $this->_resourceFactory->createResource(array($resourceConfig->getAttribute('id')));
            $acl->addResource($resource, $parent);
            if ($resourceConfig->hasChildNodes()) {
                $this->_addResourceTree($acl, $resourceConfig->childNodes, $resource);
            }
        }
    }
}
