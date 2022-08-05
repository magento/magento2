<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Multiselect;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\Js;
use Magento\Paypal\Block\Adminhtml\System\Config\MultiSelect\DisabledFundingOptions;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Config\StructurePlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DisabledFundingOptionsTest extends TestCase
{
    /**
     * @var \Magento\Paypal\Block\Adminhtml\System\Config\Multiselect\DisabledFundingOptions
     */
    private $model;

    /**
     * @var AbstractElement
     */
    private $element;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var Js|MockObject
     */
    private $jsHelper;

    /**
     * @var Config
     */
    private $config;

    protected function setUp(): void
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
            ->willReturnCallback(
                function ($param) use ($requestCountry) {
                    if ($param == StructurePlugin::REQUEST_PARAM_COUNTRY) {
                        return $requestCountry;
                    }
                    return $param;
                }
            );
        $this->config->expects($this->any())
            ->method('getMerchantCountry')
            ->willReturnCallback(
                
                    function () use ($merchantCountry) {
                        return $merchantCountry;
                    }
                
            );
        $this->model->render($this->element);
        $payPalCreditOption = [
            'value' => 'CREDIT',
            'label' => __('PayPal Credit')->getText()
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
            ['GB', 'GB', true],
            ['GB', 'US', true],
            ['GB', null, true],
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
                'label' => __('PayPal Credit')->getText()
            ],
            [
                'value' => 'CARD',
                'label' => __('PayPal Guest Checkout Credit Card Icons')->getText()
            ],
            [
                'value' => 'ELV',
                'label' => __('Elektronisches Lastschriftverfahren - German ELV')->getText()
            ]
        ];
    }
}
