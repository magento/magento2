<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Adminhtml\System\Config\Source\Inputtype;

use Magento\Eav\Helper\Data;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $helperMock;

    protected function setUp(): void
    {
        $validatorData = ['type'];
        $this->helperMock = $this->createMock(Data::class);
        $this->helperMock->expects($this->once())->method('getInputTypesValidatorData')->willReturn($validatorData);
        $this->model = new Validator($this->helperMock);
    }

    public function testAddInputType()
    {
        $this->model->addInputType('new_type');
        $this->assertEquals(['type', 'new_type'], $this->model->getHaystack());
    }
}
