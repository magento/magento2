<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Config\Backend\CreateAccount;

use Magento\Customer\Model\Config\Backend\CreateAccount\DisableAutoGroupAssignDefault;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DisableAutoGroupAssignDefaultTest extends TestCase
{
    /**
     * @var DisableAutoGroupAssignDefault
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $eavConfigMock;

    protected function setUp(): void
    {
        $this->eavConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            DisableAutoGroupAssignDefault::class,
            [
                'eavConfig' => $this->eavConfigMock,
            ]
        );
    }

    public function testAfterSave()
    {
        $value = true;

        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['save', 'setData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with('customer', 'disable_auto_group_change')
            ->willReturn($attributeMock);

        $attributeMock->expects($this->once())
            ->method('setData')
            ->with('default_value', $value);
        $attributeMock->expects($this->once())
            ->method('save');

        $this->model->setValue($value);

        $this->assertEquals($this->model, $this->model->afterSave());
    }
}
