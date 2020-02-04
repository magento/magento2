<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\CustomerData;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Captcha\CustomerData\Captcha;
use Magento\Captcha\Model\DefaultModel;
use Magento\Customer\Api\Data\CustomerInterface as CustomerData;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class CaptchaTest extends TestCase
{
    /**
     * @var CaptchaHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helperMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var Captcha
     */
    private $model;

    /**
     * @var array
     */
    private $formIds;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * Create mocks and model
     */
    protected function setUp()
    {
        $this->helperMock = $this->createMock(CaptchaHelper::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->formIds = [
            'user_login'
        ];
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Captcha::class,
            [
                'helper' => $this->helperMock,
                'formIds' => $this->formIds,
                'customerSession' => $this->customerSessionMock
            ]
        );
    }

    /**
     * Test getSectionData() when user is login and require captcha
     */
    public function testGetSectionDataWhenLoginAndRequireCaptcha()
    {
        $emailLogin = 'test@localhost.com';

        $userLoginModel = $this->createMock(DefaultModel::class);
        $userLoginModel->expects($this->any())->method('isRequired')->with($emailLogin)
            ->willReturn(true);
        $this->helperMock->expects($this->any())->method('getCaptcha')->with('user_login')->willReturn($userLoginModel);

        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')
            ->willReturn(true);

        $customerDataMock = $this->createMock(CustomerData::class);
        $customerDataMock->expects($this->any())->method('getEmail')->willReturn($emailLogin);
        $this->customerSessionMock->expects($this->any())->method('getCustomerData')
            ->willReturn($customerDataMock);

        /* Assert to test */
        $this->assertEquals(
            [
                "user_login" => [
                    "isRequired" => true,
                    "timestamp" => time()
                ]
            ],
            $this->model->getSectionData()
        );
    }
}
