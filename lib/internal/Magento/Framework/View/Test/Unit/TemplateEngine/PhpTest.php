<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\TemplateEngine;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\TemplateEngine\Php;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test template engine that enables PHP templates to be used for rendering.
 */
class PhpTest extends TestCase
{
    const TEST_PROP_VALUE = 'TEST_PROP_VALUE';

    /** @var  Php */
    protected $_phpEngine;

    /**
     * @var MockObject
     */
    protected $_helperFactoryMock;

    /**
     * Create a PHP template engine to test.
     */
    protected function setUp(): void
    {
        $this->_helperFactoryMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_phpEngine = new Php($this->_helperFactoryMock);
    }

    /**
     * Test the render() function with a very simple .phtml file.
     *
     * Note: the call() function will be covered because simple.phtml has a call to the block.
     */
    public function testRender()
    {
        $blockMock = $this->getMockBuilder(
            Template::class
        )->addMethods(
            ['testMethod']
        )->disableOriginalConstructor()
            ->getMock();

        $blockMock->expects($this->once())->method('testMethod');
        $blockMock->property = self::TEST_PROP_VALUE;

        $filename = __DIR__ . '/_files/simple.phtml';
        $actualOutput = $this->_phpEngine->render($blockMock, $filename);

//        $this->assertAttributeEquals(null, '_currentBlock', $this->_phpEngine);

        $expectedOutput = '<html>' . self::TEST_PROP_VALUE . '</html>' . PHP_EOL;
        $this->assertSame($expectedOutput, $actualOutput, 'phtml file did not render correctly');
    }

    /**
     * Test the render() function with a nonexistent filename.
     *
     * Expect an exception if the specified file does not exist.
     */
    public function testRenderException()
    {
        $this->expectException('PHPUnit\Framework\Exception');
        $blockMock = $this->getMockBuilder(
            Template::class
        )->onlyMethods(
            ['testMethod']
        )->disableOriginalConstructor()
            ->getMock();

        $filename = 'This_is_not_a_file';

        $this->_phpEngine->render($blockMock, $filename);
    }

    public function testHelperWithInvalidClass()
    {
        $this->expectException('LogicException');
        $class = DataObject::class;
        $object = $this->createMock($class);
        $this->_helperFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $class
        )->willReturn(
            $object
        );
        $this->_phpEngine->helper($class);
    }

    public function testHelperWithValidClass()
    {
        $class = AbstractHelper::class;
        $object = $this->getMockForAbstractClass($class, [], '', false);
        $this->_helperFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $class
        )->willReturn(
            $object
        );
        $this->assertEquals($object, $this->_phpEngine->helper($class));
    }
}
