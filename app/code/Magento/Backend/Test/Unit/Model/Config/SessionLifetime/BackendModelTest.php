<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Model\Config\SessionLifetime;

use Magento\Backend\Model\Config\SessionLifetime\BackendModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BackendModelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider adminSessionLifetimeDataProvider
     */
    public function testBeforeSave($value, $errorMessage = null)
    {
        /** @var BackendModel $model */
        $model = (new ObjectManager($this))->getObject(
            \Magento\Backend\Model\Config\SessionLifetime\BackendModel::class
        );
        if ($errorMessage !== null) {
            $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
            $this->expectExceptionMessage($errorMessage);
        }
        $model->setValue($value);
        $object = $model->beforeSave();
        $this->assertEquals($model, $object);
    }

    public function adminSessionLifetimeDataProvider()
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
