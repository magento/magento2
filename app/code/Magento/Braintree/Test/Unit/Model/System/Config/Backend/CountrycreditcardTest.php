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
 * Class CountrycreditcardTest
 *
 */
class CountrycreditcardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Model\System\Config\Backend\Countrycreditcard
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mathRandomMock;

    protected function setUp()
    {
        $this->resourceMock = $this->getMockForAbstractClass('\Magento\Framework\Model\ResourceModel\AbstractResource');
        $this->mathRandomMock = $this->getMockBuilder(
            '\Magento\Framework\Math\Random'
        )->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            '\Magento\Braintree\Model\System\Config\Backend\Countrycreditcard',
            [
                'mathRandom' => $this->mathRandomMock,
                'resource' => $this->resourceMock,
            ]
        );
    }

    /**
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave($value, $expectedValue)
    {
        $this->model->setValue($value);
        $this->model->beforeSave();
        $this->assertEquals($expectedValue, $this->model->getValue());
    }

    public function beforeSaveDataProvider()
    {
        return [
            'empty_value' => [
                'value' => [],
                'expected' => serialize([]),
            ],
            'not_array' => [
                'value' => [
                    '1' => 'abc',
                ],
                'expected' => serialize([]),
            ],
            'array_with_invalid_format' => [
                'value' => [
                    '1' => [
                        'country_id' => 'US',
                    ],
                ],
                'expected' => serialize([]),
            ],
            'array_with_two_countries' => [
                'value' => [
                    '1' => [
                        'country_id' => 'AF',
                        'cc_types' => [
                            'AE',
                            'VI',
                        ]
                    ],
                    '2' => [
                        'country_id' => 'US',
                        'cc_types' => [
                            'AE',
                            'VI',
                            'MA',
                        ]
                    ],
                    '__empty' => "",
                ],
                'expected' => serialize(
                    [
                        'AF' => ['AE', 'VI'],
                        'US' => ['AE', 'VI', 'MA'],
                    ]
                ),
            ],
        ];
    }

    /**
     * @dataProvider afterLoadDataProvider
     */
    public function testAfterLoad($value, $hashData, $expected)
    {
        $this->model->setValue($value);
        $index = 0;
        foreach ($hashData as $hash) {
            $this->mathRandomMock->expects($this->at($index))
                ->method('getUniqueHash')
                ->willReturn($hash);
            $index++;
        }
        $this->model->afterLoad();
        $this->assertEquals($expected, $this->model->getValue());
    }

    public function afterLoadDataProvider()
    {
        return [
            'empty' => [
                'value' => serialize([]),
                'randomHash' => [],
                'expected' => [],
            ],
            'null' => [
                'value' => null,
                'randomHash' => [],
                'expected' => null,
            ],
            'valid_data' => [
                'value' => serialize(
                    [
                        'US' => ['AE', 'VI', 'MA'],
                        'AF' => ['AE', 'MA'],
                    ]
                ),
                'randomHash' => ['hash_1', 'hash_2'],
                'expected' => [
                    'hash_1' => [
                        'country_id' => 'US',
                        'cc_types' => ['AE', 'VI', 'MA'],
                    ],
                    'hash_2' => [
                        'country_id' => 'AF',
                        'cc_types' => ['AE', 'MA'],
                    ],
                ]
            ],
        ];
    }
}
