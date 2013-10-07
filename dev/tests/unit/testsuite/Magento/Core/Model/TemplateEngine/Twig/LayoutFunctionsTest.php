<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an e-mail
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
namespace Magento\Core\Model\TemplateEngine\Twig;

class LayoutFunctionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Core\Model\TemplateEngine\Twig\LayoutFunctions */
    protected $_layoutFunctions;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_layoutMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $_blockTrackerMock;

    protected function setUp()
    {
        $this->_layoutMock = $this->getMockBuilder('Magento\Core\Model\Layout')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_blockTrackerMock = $this->getMockBuilder('Magento\Core\Model\TemplateEngine\BlockTrackerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_layoutFunctions = new \Magento\Core\Model\TemplateEngine\Twig\LayoutFunctions(
            $this->_layoutMock
        );
        $this->_layoutFunctions->setBlockTracker($this->_blockTrackerMock);
    }

    /**
     * Test that the getFunctions return arrays of appropriate types
     */
    public function testGetFunctions()
    {
        /** @var array $functions */
        $functions = $this->_layoutFunctions->getFunctions();

        $this->assertInternalType('array', $functions);
        $this->assertTrue(count($functions) >= 1, 'Functions array does not contain any elements');
        $this->assertContainsOnly('Twig_SimpleFunction', $functions, false,
            'Contains something that is not a Twig function.');
    }

    /**
     * Tests getChildHtml
     *
     * Sets childBlock to be a child of parentBlock.
     * Sets parentBlock to be the current block.
     * Then when getChildHtml is returned it should return html from childBlock
     */
    public function testGetChildHtml()
    {
        $childBlockHtml = '<p>block mock B</p>';

        $parentBlockMock = $this->getMockBuilder('Magento\Core\Block\Template')
            ->disableOriginalConstructor()
            ->getMock();
        $parentBlockMock->expects($this->any())
            ->method('getNameInLayout')
            ->will($this->returnValue('parentBlockMock'));

        $this->_layoutMock->expects($this->once())
            ->method('getChildNames')->with($this->equalTo('parentBlockMock'))
            ->will($this->returnValue(array('childBlockMock')));

        $this->_layoutMock->expects($this->once())
            ->method('renderElement')
            ->with($this->equalTo('childBlockMock'), $this->equalTo(true))
            ->will($this->returnValue($childBlockHtml));

        // Set the current block to blockA and get the child's html
        $this->_blockTrackerMock->expects($this->once())
            ->method('getCurrentBlock')
            ->will($this->returnValue($parentBlockMock));

        $actual = $this->_layoutFunctions->getChildHtml();

        $this->assertEquals($childBlockHtml, $actual, 'actual child html did not match expected');
    }

    /**
     * Tests setCurrentBlock, and getChildHtml.
     *
     * Sets childBock to be a child of parentBlock.
     * Sets parentBlock to be the current block.
     * Then when getChildHtml is returned it should return html from childBlock
     */
    public function testRenderBlockWithAlias()
    {
        $childBlockHtml = '<p>child block mock</p>';

        $parentBlockMock = $this->getMockBuilder('Magento\Core\Block\Template')
            ->disableOriginalConstructor()
            ->getMock();
        $parentBlockMock->expects($this->any())
            ->method('getNameInLayout')
            ->will($this->returnValue('parentBlockMock'));

        $this->_layoutMock->expects($this->once())
            ->method('getChildName')
            ->with($this->equalTo('parentBlockMock'), $this->equalTo('anAlias'))
            ->will($this->returnValue('childBlockMock'));

        $this->_layoutMock->expects($this->once())
            ->method('renderElement')
            ->with($this->equalTo('childBlockMock'), $this->equalTo(true))
            ->will($this->returnValue($childBlockHtml));

        $actual = $this->_layoutFunctions->renderBlock('parentBlockMock', 'anAlias');

        $this->assertEquals($childBlockHtml, $actual, 'actual child html did not match expected');
    }

    /**
     * test getBlockData with and without the block in the layout
     */
    public function testGetBlockData()
    {
        $key = 'aKey';
        $someData = 'this is some data';

        $blockMock = $this->getMockBuilder('Magento\Core\Block\Template')
            ->disableOriginalConstructor()
            ->getMock();;
        $blockMock->expects($this->any())
            ->method('getData')
            ->with($this->equalTo($key))
            ->will($this->returnValue($someData));

        $map = array(
            array('datalessBlock', NULL),
            array('dataBlock', $blockMock)
        );

        $this->_layoutMock->expects($this->any())
            ->method('getBlock')
            ->will($this->returnValueMap($map));

        $actual = $this->_layoutFunctions->getBlockData('datalessBlock');
        $this->assertNull($actual, 'datalessBlock should have returned null');
        $actual = $this->_layoutFunctions->getBlockData('dataBlock', $key);
        $this->assertEquals($someData, $actual, 'dataBlock did not return expected data');
    }

    /**
     * Test getBlockNameByAlias
     *
     * Do with mock layout returning a valid name and without
     */
    public function testGetBlockNameByAlias()
    {
        $parentName = 'ParentName';
        $goodAlias = 'anAlias';
        $badAlias = 'hasNoName';
        $name = 'aName';

        $this->_layoutMock->expects($this->at(0))
            ->method('getChildName')
            ->with($this->equalTo($parentName), $this->equalTo($goodAlias))
            ->will($this->returnValue($name));

        $this->_layoutMock->expects($this->at(1))
            ->method('getChildName')
            ->with($this->equalTo($parentName), $this->equalTo($badAlias))
            ->will($this->returnValue(false));

        $actual = $this->_layoutFunctions->getBlockNameByAlias($parentName, $goodAlias);
        $this->assertEquals($name, $actual, 'dataBlock did not return expected data');
        $actual = $this->_layoutFunctions->getBlockNameByAlias($parentName, $badAlias);
        $this->assertEquals('', $actual, 'datalessBlock should have returned empty string');
    }

    /**
     * Test getGroupChildNames
     */
    public function testGetGroupChildNames()
    {
        $parentName = 'ParentName';
        $groupName = 'GroupName';
        $aliasArray = array('aliasA' => 'blockA');

        $this->_layoutMock->expects($this->once())
            ->method('getGroupChildNames')
            ->with($this->equalTo($parentName), $this->equalTo($groupName))
            ->will($this->returnValue($aliasArray));

        $actual = $this->_layoutFunctions->getGroupChildNames($parentName, $groupName);
        $this->assertEquals($aliasArray, $actual, 'getGroupChildNames did not return expected aliasArray');
    }


}
