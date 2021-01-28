<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit;

use \Magento\Framework\View\TemplateEngineFactory;

class TemplateEngineFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $_objectManagerMock;

    /** @var  \Magento\Framework\View\TemplateEngineFactory */
    protected $_factory;

    /**
     * Setup a factory to test with an mocked object manager.
     */
    protected function setUp(): void
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
        )->willReturn(
            $engine
        );
        $this->assertSame($engine, $this->_factory->create('test'));
    }

    /**
     */
    public function testCreateUnknownEngine()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown template engine type: \'non_existing\'');

        $this->_objectManagerMock->expects($this->never())->method('create');
        $this->_factory->create('non_existing');
    }

    /**
     */
    public function testCreateInvalidEngine()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Fixture\\Module\\Model\\TemplateEngine has to implement the template engine interface');

        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Fixture\Module\Model\TemplateEngine::class
        )->willReturn(
            new \stdClass()
        );
        $this->_factory->create('test');
    }
}
