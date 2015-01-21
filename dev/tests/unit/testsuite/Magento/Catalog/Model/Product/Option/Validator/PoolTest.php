<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Validator;

class PoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\Validator\Pool
     */
    protected $pool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $defaultValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectValidatorMock;

    protected function setUp()
    {
        $this->defaultValidatorMock = $this->getMock(
            'Magento\Catalog\Model\Product\Option\Validator\DefaultValidator', [], [], '', false
        );
        $this->selectValidatorMock = $this->getMock(
            'Magento\Catalog\Model\Product\Option\Validator\Select', [], [], '', false
        );
        $this->pool = new \Magento\Catalog\Model\Product\Option\Validator\Pool(
            ['default' => $this->defaultValidatorMock, 'select' => $this->selectValidatorMock]
        );
    }

    public function testGetSelectValidator()
    {
        $this->assertEquals($this->selectValidatorMock, $this->pool->get('select'));
    }

    public function testGetDefaultValidator()
    {
        $this->assertEquals($this->defaultValidatorMock, $this->pool->get('default'));
    }
}
