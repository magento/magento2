<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Multiselect;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Data\Form\Element\AbstractElement;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\View\Helper\Js;
use \Magento\Paypal\Model\Config;
use \Magento\Paypal\Block\Adminhtml\System\Config\MultiSelect\DisabledFundingOptions;
use \Magento\Paypal\Model\Config\StructurePlugin;
use \PHPUnit\Framework\TestCase;

/**
 * Class DisabledFundingOptionsTest
 */
class DisabledFundingOptionsTest extends TestCase
{
    /**
     * @var \Magento\Paypal\Block\Adminhtml\System\Config\Multiselect\DisabledFundingOptions
     */
    private $model;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    private $element;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\View\Helper\Js|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsHelper;

    /**
     * @var \Magento\Paypal\Model\Config
     */
    private $config;

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->element = $this->getMockForAbstractClass(
            AbstractElement::class,
            [],
            '',
            false,
            true,
            true,
            ['getHtmlId', 'getElementHtml', 'getName']
        );
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->jsHelper = $this->createMock(Js::class);
        $this->config = $this->createMock(Config::class);
        $this->element->setValues($this->getDefaultFundingOptions());
        $this->model = $helper->getObject(
            DisabledFundingOptions::class,
            ['request' => $this->request, 'jsHelper' => $this->jsHelper, 'config' => $this->config]
        );
    }

    /**
     * @param null|string $requestCountry
     * @param null|string $merchantCountry
     * @param bool $shouldContainPaypalCredit
     * @dataProvider isPaypalCreditAvailableDataProvider
     */
    public function testIsPaypalCreditAvailable(
        ?string $requestCountry,
        ?string $merchantCountry,
        bool $shouldContainPaypalCredit
    ) {
        $this->request->expects($this->any())
            ->method('getParam')
            ->will($this->returnCallback(function ($param) use ($requestCountry) {
                if ($param == StructurePlugin::REQUEST_PARAM_COUNTRY) {
                    return $requestCountry;
                }
                return $param;
            }));
        $this->config->expects($this->any())
            ->method('getMerchantCountry')
            ->will($this->returnCallback(function () use ($merchantCountry) {
                return $merchantCountry;
            }));
        $this->model->render($this->element);
        $payPalCreditOption = [
            'value' => 'CREDIT',
            'label' => __('PayPal Credit')
        ];
        $elementValues = $this->element->getValues();
        if ($shouldContainPaypalCredit) {
            $this->assertContains($payPalCreditOption, $elementValues);
        } else {
            $this->assertNotContains($payPalCreditOption, $elementValues);
        }
    }

    /**
     * @return array
     */
    public function isPaypalCreditAvailableDataProvider(): array
    {
        return [
            [null, 'US', true],
            ['US', 'US', true],
            ['US', 'GB', true],
            ['GB', 'GB', false],
            ['GB', 'US', false],
            ['GB', null, false],
        ];
    }

    /**
     * @inheritdoc
     */
    private function getDefaultFundingOptions(): array
    {
        return [
            [
                'value' => 'CREDIT',
                'label' => __('PayPal Credit')
            ],
            [
                'value' => 'CARD',
                'label' => __('PayPal Guest Checkout Credit Card Icons')
            ],
            [
                'value' => 'ELV',
                'label' => __('Elektronisches Lastschriftverfahren - German ELV')
            ]
        ];
    }
}
