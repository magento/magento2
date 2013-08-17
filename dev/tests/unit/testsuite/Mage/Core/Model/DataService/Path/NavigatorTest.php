<?php
/**
 * Mage_Core_Model_DataService_Path_Navigator
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
class Mage_Core_Model_DataService_Path_NavigatorTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject  Mage_Core_Model_DataService_Path_NodeInterface */
    private $_rootNode;

    /**
     * @var Mage_Core_Model_DataService_Path_Navigator
     */
    private $_navigator;

    public function setUp()
    {
        $this->_navigator = new Mage_Core_Model_DataService_Path_Navigator();
    }

    public function testSearch()
    {
        $this->_rootNode = $this->getMockBuilder('Mage_Core_Model_DataService_Path_NodeInterface')
            ->disableOriginalConstructor()->getMock();
        $branch = $this->getMockBuilder('Mage_Core_Model_DataService_Path_NodeInterface')
            ->disableOriginalConstructor()->getMock();
        $leaf = $this->getMockBuilder('Mage_Core_Model_DataService_Path_NodeInterface')
            ->disableOriginalConstructor()->getMock();
        $this->_rootNode->expects($this->any())
            ->method('getChildNode')
            ->with('branch')
            ->will($this->returnValue($branch));
        $branch->expects($this->any())
            ->method('getChildNode')
            ->with('leaf')
            ->will($this->returnValue($leaf));

        $nodeFound = $this->_navigator->search($this->_rootNode, explode('.', 'branch.leaf'));

        $this->assertEquals($leaf, $nodeFound);
    }

    public function testSearchOfArray()
    {
        $this->_rootNode = $this->getMockBuilder('Mage_Core_Model_DataService_Path_NodeInterface')
            ->disableOriginalConstructor()->getMock();
        $branch = array();
        $leaf = 'a leaf node can be anything';
        $branch['leaf'] = $leaf;
        $this->_rootNode->expects($this->any())
            ->method('getChildNode')
            ->with('branch')
            ->will($this->returnValue($branch));

        $nodeFound = $this->_navigator->search($this->_rootNode, explode('.', 'branch.leaf'));

        $this->assertEquals($leaf, $nodeFound);
    }

    public function testSearchOfEmptyArray()
    {
        $this->_rootNode = $this->getMockBuilder('Mage_Core_Model_DataService_Path_NodeInterface')
            ->disableOriginalConstructor()->getMock();
        $branch = array();
        $this->_rootNode->expects($this->any())
            ->method('getChildNode')
            ->with('branch')
            ->will($this->returnValue($branch));

        $nodeFound = $this->_navigator->search($this->_rootNode, explode('.', 'branch.leaf'));

        $this->assertEquals(null, $nodeFound);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage invalid.leaf
     */
    public function testSearchWithInvalidPath()
    {
        $this->_rootNode = $this->getMockBuilder('Mage_Core_Model_DataService_Path_NodeInterface')
            ->disableOriginalConstructor()->getMock();
        $leaf = $this->getMockBuilder('Mage_Core_Model_DataService_Path_NodeInterface')
            ->disableOriginalConstructor()->getMock();

        $nodeFound = $this->_navigator->search($this->_rootNode, explode('.', 'invalid.leaf'));

        $this->assertEquals($leaf, $nodeFound);
    }
}