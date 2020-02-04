<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit;

use \Magento\Framework\View\TemplateEngineFactory;

class TemplateEngineFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    /** @var  \Magento\Framework\View\TemplateEngineFactory */
    protected $_factory;

    /**
     * Setup a factory to test with an mocked object manager.
     */
    protected function setUp()
    {
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_factory = new TemplateEngineFactory(
            $this->_objectManagerMock,
            ['test' => \Fixture\Module\Model\TemplateEngine::class]
        );
    }

    public function testCreateKnownEngine()
    {
        $engine = $this->createMock(\Magento\Framework\View\TemplateEngineInterface::class);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Fixture\Module\Model\TemplateEngine::class
        )->will(
            $this->returnValue($engine)
        );
        $this->assertSame($engine, $this->_factory->create('test'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown template engine type: 'non_existing'
     */
    public function testCreateUnknownEngine()
    {
        $this->_objectManagerMock->expects($this->never())->method('create');
        $this->_factory->create('non_existing');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Fixture\Module\Model\TemplateEngine has to implement the template engine interface
     */
    public function testCreateInvalidEngine()
    {
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Fixture\Module\Model\TemplateEngine::class
        )->will(
            $this->returnValue(new \stdClass())
        );
        $this->_factory->create('test');
    }
}
