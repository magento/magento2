<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Backend\Email;

use Magento\Config\Model\Config\Backend\Email\Sender;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class SenderTest extends TestCase
{
    /**
     * @var Sender
     */
    private $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Sender::class);
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
            ['Mr. Real Name', 'Mr. Real Name'],
            ['No colons:', false],
            [str_repeat('a', 256), false],
            [null, false],
            ['', false],
        ];
    }
}
