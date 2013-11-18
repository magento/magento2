<?php
/**
 * \Magento\Core\Model\DataService\Path\Navigator
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
namespace Magento\Core\Model\DataService\Path;

class NavigatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject  \Magento\Core\Model\DataService\Path\NodeInterface */
    private $_rootNode;

    /**
     * @var \Magento\Core\Model\DataService\Path\Navigator
     */
    private $_navigator;

    protected function setUp()
    {
        $this->_navigator = new \Magento\Core\Model\DataService\Path\Navigator();
    }

    public function testSearch()
    {
        $this->_rootNode = $this->getMockBuilder('Magento\Core\Model\DataService\Path\NodeInterface')
            ->disableOriginalConstructor()->getMock();
        $branch = $this->getMockBuilder('Magento\Core\Model\DataService\Path\NodeInterface')
            ->disableOriginalConstructor()->getMock();
        $leaf = $this->getMockBuilder('Magento\Core\Model\DataService\Path\NodeInterface')
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
        $this->_rootNode = $this->getMockBuilder('Magento\Core\Model\DataService\Path\NodeInterface')
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
        $this->_rootNode = $this->getMockBuilder('Magento\Core\Model\DataService\Path\NodeInterface')
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage invalid.leaf
     */
    public function testSearchWithInvalidPath()
    {
        $this->_rootNode = $this->getMockBuilder('Magento\Core\Model\DataService\Path\NodeInterface')
            ->disableOriginalConstructor()->getMock();
        $leaf = $this->getMockBuilder('Magento\Core\Model\DataService\Path\NodeInterface')
            ->disableOriginalConstructor()->getMock();

        $nodeFound = $this->_navigator->search($this->_rootNode, explode('.', 'invalid.leaf'));

        $this->assertEquals($leaf, $nodeFound);
    }
}
