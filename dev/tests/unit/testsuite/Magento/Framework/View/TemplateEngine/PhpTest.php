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
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\TemplateEngine;

class PhpTest extends \PHPUnit_Framework_TestCase
{
    const TEST_PROP_VALUE = 'TEST_PROP_VALUE';

    /** @var  \Magento\Framework\View\TemplateEngine\Php */
    protected $_phpEngine;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperFactoryMock;

    /**
     * Create a PHP template engine to test.
     */
    protected function setUp()
    {
        $this->_helperFactoryMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->_phpEngine = new \Magento\Framework\View\TemplateEngine\Php($this->_helperFactoryMock);
    }

    /**
     * Test the render() function with a very simple .phtml file.
     *
     * Note: the call() function will be covered because simple.phtml has a call to the block.
     */
    public function testRender()
    {
        $blockMock = $this->getMockBuilder(
            'Magento\Framework\View\Element\Template'
        )->setMethods(
            array('testMethod')
        )->disableOriginalConstructor()->getMock();

        $blockMock->expects($this->once())->method('testMethod');
        $blockMock->property = self::TEST_PROP_VALUE;

        $filename = __DIR__ . '/_files/simple.phtml';
        $actualOutput = $this->_phpEngine->render($blockMock, $filename);

        $this->assertAttributeEquals(null, '_currentBlock', $this->_phpEngine);

        $expectedOutput = '<html>' . self::TEST_PROP_VALUE . '</html>';
        $this->assertSame($expectedOutput, $actualOutput, 'phtml file did not render correctly');
    }

    /**
     * Test the render() function with a nonexistent filename.
     *
     * Expect an exception if the specified file does not exist.
     * @expectedException \Exception
     * @expectedExceptionMessage include(This_is_not_a_file): failed to open stream: No such file or directory
     */
    public function testRenderException()
    {
        $blockMock = $this->getMockBuilder(
            'Magento\Framework\View\Element\Template'
        )->setMethods(
            array('testMethod')
        )->disableOriginalConstructor()->getMock();

        $filename = 'This_is_not_a_file';
        $this->_phpEngine->render($blockMock, $filename);
    }

    /**
     * @expectedException \LogicException
     */
    public function testHelperWithInvalidClass()
    {
        $class = 'Magento\Framework\Object';
        $object = $this->getMock($class, array(), array(), '', false);
        $this->_helperFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $class
        )->will(
            $this->returnValue($object)
        );
        $this->_phpEngine->helper($class);
    }

    public function testHelperWithValidClass()
    {
        $class = 'Magento\Framework\App\Helper\AbstractHelper';
        $object = $this->getMockForAbstractClass($class, array(), '', false);
        $this->_helperFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $class
        )->will(
            $this->returnValue($object)
        );
        $this->assertEquals($object, $this->_phpEngine->helper($class));
    }
}
