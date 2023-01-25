<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Validator;

use Magento\Catalog\Model\Product\Option\Validator\DefaultValidator;
use Magento\Catalog\Model\Product\Option\Validator\Pool;
use Magento\Catalog\Model\Product\Option\Validator\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PoolTest extends TestCase
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var MockObject
     */
    protected $defaultValidatorMock;

    /**
     * @var MockObject
     */
    protected $selectValidatorMock;

    protected function setUp(): void
    {
        $this->defaultValidatorMock = $this->createMock(
            DefaultValidator::class
        );
        $this->selectValidatorMock = $this->createMock(Select::class);
        $this->pool = new Pool(
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
