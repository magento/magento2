<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $validator1 = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validator1->expects($this->once())->method('validate')->with($className);
        $validator2 = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validator2->expects($this->once())->method('validate')->with($className);

        $this->model->add($validator1);
        $this->model->add($validator2);
        $this->model->validate($className);
    }
}
