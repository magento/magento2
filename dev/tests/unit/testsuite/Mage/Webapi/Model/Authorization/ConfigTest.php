<?php
/**
 * Test class for Mage_Webapi_Model_Authorization_Config
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
class Mage_Webapi_Model_Authorization_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webapi_Model_Authorization_Config
     */
    protected $_model;

    /**
     * @var Magento_Acl_Config_Reader|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configReader;

    /**
     * @var Mage_Webapi_Model_Authorization_Config_Reader_Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerFactory;

    /**
     * @var Mage_Core_Model_Config|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    /**
     * Set up before test.
     */
    protected function setUp()
    {
        $helper = new Magento_Test_Helper_ObjectManager($this);

        $this->_config = $this->getMockBuilder('Mage_Core_Model_Config_Modules_Reader')
            ->disableOriginalConstructor()
            ->setMethods(array('getModuleConfigurationFiles'))
            ->getMock();

        $this->_readerFactory = $this->getMockBuilder('Mage_Webapi_Model_Authorization_Config_Reader_Factory')
            ->disableOriginalConstructor()
            ->setMethods(array('createReader'))
            ->getMock();

        $this->_configReader = $this->getMockBuilder('Magento_Acl_Config_Reader')
            ->disableOriginalConstructor()
            ->setMethods(array('getAclResources'))
            ->getMock();

        $this->_model = $helper->getObject('Mage_Webapi_Model_Authorization_Config', array(
            'moduleReader' => $this->_config,
            'readerFactory' => $this->_readerFactory
        ));

        $this->_config->expects($this->any())
            ->method('getModuleConfigurationFiles')
            ->will($this->returnValue(array()));

        $this->_readerFactory->expects($this->any())
            ->method('createReader')
            ->will($this->returnValue($this->_configReader));
    }

    /**
     * Test for Mage_Webapi_Model_Authorization_Config::getAclResources().
     */
    public function testGetAclResources()
    {
        $aclResources = new DOMDocument();
        $aclResources->load(__DIR__ . DIRECTORY_SEPARATOR .  '..'
            . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'acl.xml');
        $this->_configReader->expects($this->once())
            ->method('getAclResources')
            ->will($this->returnValue($aclResources));

        $expectedResources = array(
            'Mage_Webapi',
            'customer',
            'customer/create',
            'customer/delete',
            'customer/get',
            'customer/update'
        );
        $resources = $this->_model->getAclResources();

        $this->assertInstanceOf('DOMNodeList', $resources);
        $actualResources = $this->getResources($resources);
        sort($expectedResources);
        sort($actualResources);
        $this->assertEquals($expectedResources, $actualResources);
    }

    /**
     * Get resources array recursively.
     *
     * @param DOMNodeList $resources
     * @return array
     */
    public function getResources($resources)
    {
        $resourceArray = array();
        /** @var $resource DOMElement */
        foreach ($resources as $resource) {
            if (!($resource instanceof DOMElement)) {
                continue;
            }
            $resourceArray = array_merge($resourceArray, array($resource->getAttribute('id')));
            if ($resource->hasChildNodes()) {
                $resourceArray = array_merge($resourceArray, $this->getResources($resource->childNodes));
            }
        }
        return $resourceArray;
    }

    /**
     * Test for Mage_Webapi_Model_Authorization_Config::getAclVirtualResources.
     */
    public function testGetAclVirtualResources()
    {
        $aclResources = new DOMDocument();
        $aclResources->load(__DIR__ . DIRECTORY_SEPARATOR .  '..'
            . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'acl.xml');
        $this->_configReader->expects($this->once())
            ->method('getAclResources')
            ->will($this->returnValue($aclResources));

        $expectedResources = array(array(
            'id' => 'customer/list',
            'parent' => 'customer/get'
        ));
        $resources = $this->_model->getAclVirtualResources();

        $this->assertInstanceOf('DOMNodeList', $resources);
        $actualResources = array();
        foreach ($resources as $resourceConfig) {
            if (!($resourceConfig instanceof DOMElement)) {
                continue;
            }
            $actualResources[] = array(
                'id' => $resourceConfig->getAttribute('id'),
                'parent' => $resourceConfig->getAttribute('parent')
            );
        }
        sort($expectedResources);
        sort($actualResources);
        $this->assertEquals($expectedResources, $actualResources);
    }

    /**
     * Test for Mage_Webapi_Model_Authorization_Config::getAclResourcesAsArray.
     *
     * @dataProvider aclResourcesDataProvider
     * @param string $actualXmlFile
     * @param bool $includeRoot
     * @param array $expectedResources
     */
    public function testGetAclResourcesAsArray($actualXmlFile, $includeRoot, $expectedResources)
    {
        $actualAclResources = new DOMDocument();
        $actualAclResources->load($actualXmlFile);

        $this->_configReader->expects($this->once())
            ->method('getAclResources')
            ->will($this->returnValue($actualAclResources));

        $this->assertEquals($expectedResources, $this->_model->getAclResourcesAsArray($includeRoot));
    }

    /**
     * @return array
     */
    public function aclResourcesDataProvider()
    {
        $aclResourcesArray = array (
            'id' => 'Mage_Webapi',
            'text' => '',
            'children' => array(
                array(
                    'id' => 'customer',
                    'text' => 'Manage Customers',
                    'sortOrder' => 20,
                    'children' => array(
                        array(
                            'id' => 'customer/update',
                            'text' => 'Edit Customer',
                            'sortOrder' => 10,
                            'children' => array(),
                        ),
                        array(
                            'id' => 'customer/get',
                            'text' => 'Get Customer',
                            'sortOrder' => 20,
                            'children' => array(),
                        ),
                        array(
                            'id' => 'customer/create',
                            'text' => 'Create Customer',
                            'sortOrder' => 30,
                            'children' => array(),
                        ),
                        array(
                            'id' => 'customer/delete',
                            'text' => 'Delete Customer',
                            'children' => array(),
                        ),
                    ),
                ),
            ),
        );
        return array(
            array(
                'actualXmlFile' => __DIR__ . DIRECTORY_SEPARATOR .  '..'
                    . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'acl.xml',
                'includeRoot' => true,
                'expectedResources' => $aclResourcesArray
            ),
            array(
                'actualXmlFile' =>__DIR__ . DIRECTORY_SEPARATOR .  '..'
                    . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'acl.xml',
                'includeRoot' => false,
                'expectedResources' => $aclResourcesArray['children'],
            )
        );
    }

    /**
     * Test for _getSortedBySortOrder method.
     *
     * @dataProvider getSortedBySortOrderDataProvider
     * @param array $originArray
     * @param array $sortedArray
     */
    public function testGetSortedBySortOrder($originArray, $sortedArray)
    {
        $methodReflection = new ReflectionMethod($this->_model, '_getSortedBySortOrder');
        $methodReflection->setAccessible(true);
        $this->assertEquals($sortedArray, $methodReflection->invoke($this->_model, $originArray));
    }

    /**
     * @return array
     */
    public function getSortedBySortOrderDataProvider()
    {
        return array(
            array(
                array(
                    array('name' => 'A', 'sortOrder' => 2),
                    array('name' => 'B', 'sortOrder' => 1),
                    array('name' => 'C', 'sortOrder' => 1),
                    array('name' => 'D', 'sortOrder' => 1),
                    array('name' => 'E', 'sortOrder' => 0)
                ),
                array(
                    array('name' => 'E', 'sortOrder' => 0),
                    array('name' => 'B', 'sortOrder' => 1),
                    array('name' => 'C', 'sortOrder' => 1),
                    array('name' => 'D', 'sortOrder' => 1),
                    array('name' => 'A', 'sortOrder' => 2),
                )
            ),
            array(
                array(
                    array('name' => 'A'),
                    array('name' => 'B'),
                    array('name' => 'C', 'sortOrder' => 1),
                    array('name' => 'D'),
                    array('name' => 'E'),
                    array('name' => 'F', 'sortOrder' => -1)
                ),
                array(
                    array('name' => 'F', 'sortOrder' => -1),
                    array('name' => 'C', 'sortOrder' => 1),
                    array('name' => 'A'),
                    array('name' => 'B'),
                    array('name' => 'D'),
                    array('name' => 'E'),
                )
            )
        );
    }
}
