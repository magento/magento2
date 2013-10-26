<?php
/**
 * Test for \Magento\Webapi\Block\Adminhtml\Role\Edit\Tab\Resource block.
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

namespace Magento\Webapi\Block\Adminhtml\Role\Edit\Tab;

/**
 * @magentoAppArea adminhtml
 */
class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ruleResource;

    /**
     * @var \Magento\Core\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var \Magento\Webapi\Block\Adminhtml\Role\Edit\Tab\Resource
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        $this->_resourceProvider = $this->getMock('Magento\Webapi\Model\Acl\Resource\ProviderInterface');

        $this->_ruleResource = $this->getMockBuilder('Magento\Webapi\Model\Resource\Acl\Rule')
            ->disableOriginalConstructor()
            ->setMethods(array('getResourceIdsByRole'))
            ->getMock();

        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_layout = $this->_objectManager->get('Magento\View\LayoutInterface');
        $this->_blockFactory = $this->_objectManager->get('Magento\Core\Model\BlockFactory');
        $this->_block = $this->_blockFactory->createBlock('Magento\Webapi\Block\Adminhtml\Role\Edit\Tab\Resource',
            array(
                'resourceProvider' => $this->_resourceProvider,
                'ruleResource' => $this->_ruleResource
            )
        );
        $this->_layout->addBlock($this->_block);
    }

    protected function tearDown()
    {
        $this->_objectManager->removeSharedInstance('Magento\View\LayoutInterface');
        unset($this->_objectManager, $this->_layout, $this->_resourceProvider, $this->_blockFactory, $this->_block);
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
        $apiRole = new \Magento\Object(array(
            'role_id' => 1
        ));
        $apiRole->setIdFieldName('role_id');

        $this->_block->setApiRole($apiRole);

        $this->_resourceProvider->expects($this->once())
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
