<?php
/**
 * Test class for Mage_Webapi_Model_Authorization_Loader_Resource
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Authorization_Loader_ResourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webapi_Model_Authorization_Loader_Resource
     */
    protected $_model;

    /**
     * @var Magento_Acl
     */
    protected $_acl;

    /**
     * @var Magento_Test_Helper_ObjectManager
     */
    protected $_helper;

    /**
     * @var Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader
     */
    protected $_config;

    /**
     * Set up before test.
     */
    protected function setUp()
    {
        $fixturePath = __DIR__ . '/../../_files/';
        $this->_helper = new Magento_Test_Helper_ObjectManager($this);

        $resource = new Magento_Acl_Resource('test resource');

        /** @var $resourceFactory Magento_Acl_ResourceFactory */
        $resourceFactory = $this->getMock('Magento_Acl_ResourceFactory',
            array('createResource'), array(), '', false);
        $resourceFactory->expects($this->any())
            ->method('createResource')
            ->will($this->returnValue($resource));

        $this->_config = $this->getMock('Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader',
            array('getAclResources', 'getAclVirtualResources'), array(), '', false);
        $this->_config->expects($this->once())
            ->method('getAclResources')
            ->will($this->returnValue(include $fixturePath . 'acl.php'));

        $this->_model = $this->_helper->getObject('Mage_Webapi_Model_Authorization_Loader_Resource', array(
            'resourceFactory' => $resourceFactory,
            'reader' => $this->_config,
        ));

        $this->_acl = $this->getMock(
            'Magento_Acl', array('has', 'addResource', 'deny', 'getResources'), array(), '', false
        );
    }

    /**
     * Test for Mage_Webapi_Model_Authorization_Loader_Resource::populateAcl.
     */
    public function testPopulateAcl()
    {
        $this->_config->expects($this->once())
            ->method('getAclVirtualResources')
            ->will($this->returnValue($this->getResourceXPath()->query('/config/mapping/*')));

        $this->_acl->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue(array('customer/get', 'customer/create')));
        $this->_acl->expects($this->exactly(2))
            ->method('deny')
            ->with(null, $this->logicalOr('customer/get', 'customer/create'));
        $this->_acl->expects($this->exactly(2))
            ->method('has')
            ->with($this->logicalOr('customer/get', 'customer/list'))
            ->will($this->returnValueMap(array(
                array('customer/get', true),
                array('customer/list', false)
            )));
        $this->_acl->expects($this->exactly(7))
            ->method('addResource');

        $this->_model->populateAcl($this->_acl);
    }

    /**
     * Test for Mage_Webapi_Model_Authorization_Loader_Resource::populateAcl with invalid Virtual resources DOM.
     */
    public function testPopulateAclWithInvalidDOM()
    {
        $this->_config->expects($this->once())
            ->method('getAclVirtualResources')
            ->will($this->returnValue(array(3)));

        $this->_acl->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue(array('customer/get', 'customer/list')));
        $this->_acl->expects($this->exactly(2))
            ->method('deny')
            ->with(null, $this->logicalOr('customer/get', 'customer/list'));

        $this->_model->populateAcl($this->_acl);
    }

    /**
     * Get Resources DOMXPath from fixture.
     *
     * @return DOMXPath
     */
    public function getResourceXPath()
    {
        $aclResources = new DOMDocument();
        $aclResources->load(__DIR__ . DIRECTORY_SEPARATOR .  '..' . DIRECTORY_SEPARATOR .  '..'
            . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'acl.xml');
        return new DOMXPath($aclResources);
    }
}
