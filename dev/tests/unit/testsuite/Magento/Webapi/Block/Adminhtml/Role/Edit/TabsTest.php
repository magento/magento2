<?php
/**
 * Test class for \Magento\Webapi\Block\Adminhtml\Role\Edit\Tabs
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
namespace Magento\Webapi\Block\Adminhtml\Role\Edit;

class TabsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Block\Adminhtml\Role\Edit\Tabs
     */
    protected $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\Layout
     */
    protected $_layout;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\App\RequestInterface
     */
    protected $_request;

    protected function setUp()
    {
        /** @var \Magento\Backend\Model\Url|PHPUnit_Framework_MockObject_MockObject $urlBuilder */
        $urlBuilder = $this->getMockBuilder('Magento\Backend\Model\Url')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_layout = $this->getMockBuilder('Magento\Core\Model\Layout')
            ->disableOriginalConstructor()
            ->setMethods(array('helper', 'getBlock'))
            ->getMock();

        $backendData = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false);
        $this->_request = $this->getMockForAbstractClass('Magento\App\RequestInterface',
            array($backendData), '', false, false, true, array('getParam'));

        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_block = $this->_helper->getObject('Magento\Webapi\Block\Adminhtml\Role\Edit\Tabs', array(
            'urlBuilder' => $urlBuilder,
            'layout' => $this->_layout,
            'request' => $this->_request
        ));
    }

    /**
     * Test _construct method.
     */
    public function testConstruct()
    {
        $this->assertEquals('page_tabs', $this->_block->getId());
        $this->assertEquals('edit_form', $this->_block->getDestElementId());
        $this->assertEquals('Role Information', $this->_block->getTitle());
    }

    /**
     * Test for _beforeToHtml method.
     *
     * @dataProvider beforeToHtmlDataProvider
     * @param object $apiRole
     * @param array $expectedTabIds
     */
    public function testBeforeToHtml($apiRole, $expectedTabIds)
    {
        $this->_block->setApiRole($apiRole);

        $mainBlock = $this->_helper->getObject('Magento\Core\Block\Text');
        $resourceBlock = $this->_helper->getObject('Magento\Core\Block\Text');
        $userBlock = $this->_helper->getObject('Magento\Core\Block\Text');

        $this->_layout->expects($this->any())
            ->method('getBlock')
            ->will($this->returnValueMap(array(
            array('webapi.role.edit.tab.main', $mainBlock),
            array('webapi.role.edit.tab.resource', $resourceBlock),
            array('webapi.role.edit.tab.users.grid', $userBlock),
        )));

        $this->_request->expects($this->any())->method('getParam')->will($this->returnValueMap(array(
            array('active_tab', null, 'main_section')
        )));

        // TODO: do checks using toHtml() when DI is implemented for abstract blocks
        $toHtmlMethod = new \ReflectionMethod($this->_block, '_beforeToHtml');
        $toHtmlMethod->setAccessible(true);
        $toHtmlMethod ->invoke($this->_block);

        $this->assertEquals($expectedTabIds, $this->_block->getTabsIds());
        $this->assertEquals($apiRole, $mainBlock->getApiRole());
        $this->assertEquals($apiRole, $resourceBlock->getApiRole());
    }

    /**
     * @return array
     */
    public function beforeToHtmlDataProvider()
    {
        return array(
            array(
                new \Magento\Object(array(
                    'role_id' => 1,
                    'role_name' => 'some_role'
                )),
                array('main_section', 'resource_section', 'user_section'),
            )
        );
    }
}
