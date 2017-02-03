<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model\System\Config\Backend;

use Magento\Braintree\Model\Config;
use Magento\Braintree\Model\PaymentMethod;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class UsevaultTest
 *
 */
class UsevaultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Model\System\Config\Backend\Usevault
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            '\Magento\Braintree\Model\System\Config\Backend\Usevault',
            [
            ]
        );
    }

    /**
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave($data, $value, $expectedValue)
    {
        $this->model->setData($data);
        $this->model->setValue($value);
        $this->model->beforeSave();
        $this->assertEquals($expectedValue, $this->model->getValue());
    }

    public function beforeSaveDataProvider()
    {
        return [
            'not_active' => [
                'data' => [
                    'groups' => [
                        'braintree' => [
                            'fields' => [
                                'active' => [
                                    'value' => 0,
                                ],
                            ],
                        ],
                    ],
                ],
                'value' => 1,
                'expected' => 0,
            ],
            'active_enabled' => [
                'data' => [
                    'groups' => [
                        'braintree' => [
                            'fields' => [
                                'active' => [
                                    'value' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
                'value' => 1,
                'expected' => 1,
            ],
            'active_disabled' => [
                'data' => [
                    'groups' => [
                        'braintree' => [
                            'fields' => [
                                'active' => [
                                    'value' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
                'value' => 0,
                'expected' => 0,
            ],
        ];
    }
}
