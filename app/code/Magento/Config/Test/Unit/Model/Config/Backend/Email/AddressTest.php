<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Backend\Email;

use Magento\Config\Model\Config\Backend\Email\Address;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Address
     */
    private $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Address::class);
    }

    /**
     * @dataProvider beforeSaveDataProvider
     * @param string|null $value
     * @param string|bool $expectedValue false if exception to be thrown
     * @return void
     */
    public function testBeforeSave($value, $expectedValue)
    {
        $this->model->setValue($value);

        if ($expectedValue === false) {
            $this->expectException(LocalizedException::class);
        }

        $this->model->beforeSave();
        $this->assertEquals($expectedValue, $this->model->getValue());
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            ['someone@magento.com', 'someone@magento.com'],
            ['real+email@magento.com', 'real+email@magento.com'],
            ['not.a.real.email', false],
            [null, false],
            ['', false]
        ];
    }
}
