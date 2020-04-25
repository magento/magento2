<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\TemplateEngineFactory;
use Magento\Framework\View\TemplateEngineInterface;
use \Magento\Framework\View\TemplateEnginePool;

class TemplateEnginePoolTest extends TestCase
{
    /**
     * @var TemplateEnginePool
     */
    protected $_model;

    /**
     * @varMockObject
     */
    protected $_factory;

    protected function setUp(): void
    {
        $this->_factory = $this->createMock(TemplateEngineFactory::class);
        $this->_model = new TemplateEnginePool($this->_factory);
    }

    public function testGet()
    {
        $engine = $this->createMock(TemplateEngineInterface::class);
        $this->_factory->expects($this->once())->method('create')->with('test')->will($this->returnValue($engine));
        $this->assertSame($engine, $this->_model->get('test'));
        // Make sure factory is invoked only once and the same instance is returned afterwards
        $this->assertSame($engine, $this->_model->get('test'));
    }
}
