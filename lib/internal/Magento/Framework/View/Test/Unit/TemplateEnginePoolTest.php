<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\View\TemplateEngineFactory;
use Magento\Framework\View\TemplateEngineInterface;
use Magento\Framework\View\TemplateEnginePool;
use PHPUnit\Framework\TestCase;

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
        $engine = $this->getMockForAbstractClass(TemplateEngineInterface::class);
        $this->_factory->expects($this->once())->method('create')->with('test')->willReturn($engine);
        $this->assertSame($engine, $this->_model->get('test'));
        // Make sure factory is invoked only once and the same instance is returned afterwards
        $this->assertSame($engine, $this->_model->get('test'));
    }
}
