<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Model\Config\Form;

use Magento\Captcha\Model\Config\Form\Backend;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BackendTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Backend
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
            Backend::class,
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
            ->with('captcha/backend/areas', 'default')
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
            'Empty captcha backend areas' => [
                '',
                []
            ],
            'With two captcha backend area' => [
                [
                    'backend_login' => [
                        'label' => 'Admin Login'
                    ],
                    'backend_forgotpassword' => [
                        'label' => 'Admin Forgot Password'
                    ]
                ],
                [
                    [
                        'label' => 'Admin Login',
                        'value' => 'backend_login'
                    ],
                    [
                        'label' => 'Admin Forgot Password',
                        'value' => 'backend_forgotpassword'
                    ]
                ]
            ]
        ];
    }
}
