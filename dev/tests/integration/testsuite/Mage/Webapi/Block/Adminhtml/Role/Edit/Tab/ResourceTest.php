<?php
/**
 * Test for Mage_Webapi_Block_Adminhtml_Role_Edit_Tab_Resource block.
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

/**
 * @magentoAppArea adminhtml
 */
class Mage_Webapi_Block_Adminhtml_Role_Edit_Tab_ResourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configReader;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ruleResource;

    /**
     * @var Mage_Core_Model_BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var Mage_Webapi_Block_Adminhtml_Role_Edit_Tab_Resource
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        $this->_configReader = $this->getMockBuilder('Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader')
            ->disableOriginalConstructor()
            ->setMethods(array('getAclResources'))
            ->getMock();

        $this->_ruleResource = $this->getMockBuilder('Mage_Webapi_Model_Resource_Acl_Rule')
            ->disableOriginalConstructor()
            ->setMethods(array('getResourceIdsByRole'))
            ->getMock();

        $this->_objectManager = Mage::getObjectManager();
        $this->_layout = $this->_objectManager->get('Mage_Core_Model_Layout');
        $this->_blockFactory = $this->_objectManager->get('Mage_Core_Model_BlockFactory');
        $this->_block = $this->_blockFactory->createBlock('Mage_Webapi_Block_Adminhtml_Role_Edit_Tab_Resource', array(
            'configReader' => $this->_configReader,
            'ruleResource' => $this->_ruleResource
        ));
        $this->_layout->addBlock($this->_block);
    }

    protected function tearDown()
    {
        $this->_objectManager->removeSharedInstance('Mage_Core_Model_Layout');
        unset($this->_objectManager, $this->_layout, $this->_configReader, $this->_blockFactory, $this->_block);
    }

    /**
     * Test _prepareForm method.
     *
     * @dataProvider prepareFormDataProvider
     * @param array $originResTree
     * @param array $selectedRes
     * @param array $expectedRes
     */
    public function testPrepareForm($originResTree, $selectedRes, $expectedRes)
    {
        // TODO: Move to unit tests after MAGETWO-4015 complete.
        $apiRole = new Varien_Object(array(
            'role_id' => 1
        ));
        $apiRole->setIdFieldName('role_id');

        $this->_block->setApiRole($apiRole);

        $this->_configReader->expects($this->once())
            ->method('getAclResources')
            ->will($this->returnValue($originResTree));

        $this->_ruleResource->expects($this->once())
            ->method('getResourceIdsByRole')
            ->with($apiRole->getId())
            ->will($this->returnValue($selectedRes));

        $this->_block->toHtml();

        $this->assertEquals($expectedRes, $this->_block->getResourcesTree());
    }

    /**
     * @return array
     */
    public function prepareFormDataProvider()
    {
        $resourcesTree = array(
            array('id' => 'All'),
            array(
                'id' => 'Admin',
                'children' => array(
                    array(
                        'id' => 'customer',
                        'title' => 'Manage Customers',
                        'sortOrder' => 20,
                        'children' => array(
                            array(
                                'id' => 'customer/get',
                                'title' => 'Get Customer',
                                'sortOrder' => 20,
                                'children' => array(),
                            ),
                            array(
                                'id' => 'customer/create',
                                'title' => 'Create Customer',
                                'sortOrder' => 30,
                                'children' => array(),
                            )
                        )
                    )
                )
            )
        );
        $expected = array(
            array(
                'id' => 'customer',
                'text' => 'Manage Customers',
                'children' => array(
                    array(
                        'id' => 'customer/get',
                        'text' => 'Get Customer',
                        'children' => array()
                    ),
                    array(
                        'id' => 'customer/create',
                        'text' => 'Create Customer',
                        'children' => array()
                    ),
                )
            )
        );
        $expectedSelected = $expected;
        $expectedSelected[0]['children'][0]['checked'] = true;
        return array(
            'Empty Selected Resources' => array(
                'originResourcesTree' => $resourcesTree,
                'selectedResources' => array(),
                'expectedResourcesTree' => $expected
            ),
            'One Selected Resource' => array(
                'originResourcesTree' => $resourcesTree,
                'selectedResources' => array('customer/get'),
                'expectedResourcesTree' => $expectedSelected
            )
        );
    }
}
