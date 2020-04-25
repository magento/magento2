<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\TemplateEngineInterface;
use \Magento\Framework\View\TemplateEngineFactory;

class TemplateEngineFactoryTest extends TestCase
{
    /** @var MockObject */
    protected $_objectManagerMock;

    /** @var  TemplateEngineFactory */
    protected $_factory;

    /**
     * Setup a factory to test with an mocked object manager.
     */
    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->_factory = new TemplateEngineFactory(
            $this->_objectManagerMock,
            ['test' => \Fixture\Module\Model\TemplateEngine::class]
        );
    }

    public function testCreateKnownEngine()
    {
        $engine = $this->createMock(TemplateEngineInterface::class);
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

    public function testCreateUnknownEngine()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unknown template engine type: \'non_existing\'');
        $this->_objectManagerMock->expects($this->never())->method('create');
        $this->_factory->create('non_existing');
    }

    public function testCreateInvalidEngine()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage(
            'Fixture\Module\Model\TemplateEngine has to implement the template engine interface'
        );
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
