<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\TemplateEngine;

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
        $this->_helperFactoryMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
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
            \Magento\Framework\View\Element\Template::class
        )->setMethods(
            ['testMethod']
        )->disableOriginalConstructor()->getMock();

        $blockMock->expects($this->once())->method('testMethod');
        $blockMock->property = self::TEST_PROP_VALUE;

        $filename = __DIR__ . '/_files/simple.phtml';
        $actualOutput = $this->_phpEngine->render($blockMock, $filename);

        $this->assertAttributeEquals(null, '_currentBlock', $this->_phpEngine);

        $expectedOutput = '<html>' . self::TEST_PROP_VALUE . '</html>' . PHP_EOL;
        $this->assertSame($expectedOutput, $actualOutput, 'phtml file did not render correctly');
    }

    /**
     * Test the render() function with a nonexistent filename.
     *
     * Expect an exception if the specified file does not exist.
     * @expectedException \Exception
     */
    public function testRenderException()
    {
        $blockMock = $this->getMockBuilder(
            \Magento\Framework\View\Element\Template::class
        )->setMethods(
            ['testMethod']
        )->disableOriginalConstructor()->getMock();

        $filename = 'This_is_not_a_file';
        $this->_phpEngine->render($blockMock, $filename);
    }

    /**
     * @expectedException \LogicException
     */
    public function testHelperWithInvalidClass()
    {
        $class = \Magento\Framework\DataObject::class;
        $object = $this->getMock($class, [], [], '', false);
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
        $class = \Magento\Framework\App\Helper\AbstractHelper::class;
        $object = $this->getMockForAbstractClass($class, [], '', false);
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
