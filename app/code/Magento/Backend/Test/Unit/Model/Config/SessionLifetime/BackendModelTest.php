<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Config\SessionLifetime;

use Magento\Backend\Model\Config\SessionLifetime\BackendModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BackendModelTest extends TestCase
{
    /**
     */
    #[DataProvider('adminSessionLifetimeDataProvider')]
    public function testBeforeSave($value, $errorMessage = null)
    {
        /** @var BackendModel $model */
        $model = (new ObjectManager($this))->getObject(
            BackendModel::class
        );
        if ($errorMessage !== null) {
            $this->expectException(LocalizedException::class);
            $this->expectExceptionMessage($errorMessage);
        }
        $model->setValue($value);
        $object = $model->beforeSave();
        $this->assertEquals($model, $object);
    }

    /**
     * @return array
     */
    public static function adminSessionLifetimeDataProvider()
    {
        return [
            [
                BackendModel::MIN_LIFETIME - 1,
                'The Admin session lifetime is invalid. Set the lifetime to 60 seconds or longer and try again.'
            ],
            [
                BackendModel::MAX_LIFETIME + 1,
                'The Admin session lifetime is invalid. '
                . 'Set the lifetime to 31536000 seconds (one year) or shorter and try again.'
            ],
            [
                900
            ]
        ];
    }
}
