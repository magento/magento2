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

/**
 * Test class to cover \Magento\Captcha\CustomerData\Captcha
 *
 * Class \Magento\Captcha\Test\Unit\CustomerData\CaptchaTest
 */
class CaptchaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CaptchaHelper | \PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @var CustomerSession | \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSession;

    /**
     * @var CustomerData | \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerData;

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
        $this->helper = $this->createMock(CaptchaHelper::class);
        $this->customerSession = $this->createMock(CustomerSession::class);
        $this->formIds = [
            'user_login'
        ];
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Captcha::class,
            [
                'helper' => $this->helper,
                'formIds' => $this->formIds,
                'customerSession' => $this->customerSession
            ]
        );
    }

    /**
     * Test getSectionData() when user is login and require captcha
     */
    public function testGetSectionData()
    {
        $emailLogin = 'test@localhost.com';

        $userLoginModel = $this->createMock(DefaultModel::class);
        $userLoginModel->expects($this->any())->method('isRequired')->with($emailLogin)
            ->willReturn(true);
        $this->helper->expects($this->any())->method('getCaptcha')->with('user_login')->willReturn($userLoginModel);

        $this->customerSession->expects($this->any())->method('isLoggedIn')
            ->willReturn(true);

        $this->customerData = $this->createMock(CustomerData::class);
        $this->customerData->expects($this->any())->method('getEmail')->willReturn($emailLogin);
        $this->customerSession->expects($this->any())->method('getCustomerData')
            ->willReturn($this->customerData);

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
