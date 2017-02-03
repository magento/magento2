<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit;

use \Magento\Framework\View\TemplateEnginePool;

class TemplateEnginePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateEnginePool
     */
    protected $_model;

    /**
     * @var\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factory;

    protected function setUp()
    {
        $this->_factory = $this->getMock('Magento\Framework\View\TemplateEngineFactory', [], [], '', false);
        $this->_model = new TemplateEnginePool($this->_factory);
    }

    public function testGet()
    {
        $engine = $this->getMock('Magento\Framework\View\TemplateEngineInterface');
        $this->_factory->expects($this->once())->method('create')->with('test')->will($this->returnValue($engine));
        $this->assertSame($engine, $this->_model->get('test'));
        // Make sure factory is invoked only once and the same instance is returned afterwards
        $this->assertSame($engine, $this->_model->get('test'));
    }
}
