<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Model\Config\Form;

use Magento\Captcha\Model\Config\Form\Frontend;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FrontendTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Frontend
     */
    private $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * Setup Environment For Testing
     */
    protected function setUp()
    {
        $this->configMock = $this->createMock(ScopeConfigInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            Frontend::class,
            [
                'config' => $this->configMock
            ]
        );
    }

    /**
     * Test toOptionArray() with data provider below
     *
     * @param string|array $config
     * @param array $expectedResult
     * @dataProvider toOptionArrayDataProvider
     */
    public function testToOptionArray($config, $expectedResult)
    {
        $this->configMock->expects($this->any())->method('getValue')
            ->with('captcha/frontend/areas', 'default')
            ->willReturn($config);

        $this->assertEquals($expectedResult, $this->model->toOptionArray());
    }

    /**
     * Data Provider for testing toOptionArray()
     *
     * @return array
     */
    public function toOptionArrayDataProvider()
    {
        return [
            'Empty captcha frontend areas' => [
                '',
                []
            ],
            'With two captcha frontend area' => [
                [
                    'product_sendtofriend_form' => [
                        'label' => 'Send To Friend Form'
                    ],
                    'sales_rule_coupon_request' => [
                        'label' => 'Applying coupon code'
                    ]
                ],
                [
                    [
                        'label' => 'Send To Friend Form',
                        'value' => 'product_sendtofriend_form'
                    ],
                    [
                        'label' => 'Applying coupon code',
                        'value' => 'sales_rule_coupon_request'
                    ]
                ]
            ]
        ];
    }
}
