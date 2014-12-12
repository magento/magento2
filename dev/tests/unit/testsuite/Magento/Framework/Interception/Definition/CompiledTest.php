<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Interception\Definition;

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
