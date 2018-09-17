<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Test\Unit\Definition;

class CompiledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $_definitions = ['type' => 'definitions'];

    /**
     * @covers \Magento\Framework\Interception\Definition\Compiled::getMethodList
     * @covers \Magento\Framework\Interception\Definition\Compiled::__construct
     */
    public function testGetMethodList()
    {
        $model = new \Magento\Framework\Interception\Definition\Compiled($this->_definitions);
        $this->assertEquals('definitions', $model->getMethodList('type'));
    }
}
