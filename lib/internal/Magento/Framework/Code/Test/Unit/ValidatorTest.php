<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Test\Unit;

use PHPUnit\Framework\TestCase;
use Magento\Framework\Code\ValidatorInterface;
use \Magento\Framework\Code\Validator;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new Validator();
    }

    public function testValidate()
    {
        $className = 'Same\Class\Name';
        $validator1 = $this->createMock(ValidatorInterface::class);
        $validator1->expects($this->once())->method('validate')->with($className);
        $validator2 = $this->createMock(ValidatorInterface::class);
        $validator2->expects($this->once())->method('validate')->with($className);

        $this->model->add($validator1);
        $this->model->add($validator2);
        $this->model->validate($className);
    }
}
