<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Model\AgreementsProvider;

class AgreementsConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\AgreementsConfigProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $agreementsRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->agreementsRepositoryMock = $this->getMock(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface::class,
            [],
            [],
            '',
            false
        );
        $this->escaperMock = $this->getMock(\Magento\Framework\Escaper::class, [], [], '', false);

        $this->model = new \Magento\CheckoutAgreements\Model\AgreementsConfigProvider(
            $this->scopeConfigMock,
            $this->agreementsRepositoryMock,
            $this->escaperMock
        );
    }

    public function testGetConfigIfContentIsHtml()
    {
        $content = 'content';
        $checkboxText = 'checkbox_text';
        $mode = \Magento\CheckoutAgreements\Model\AgreementModeOptions::MODE_AUTO;
        $agreementId = 100;
        $expectedResult = [
            'checkoutAgreements' => [
                'isEnabled' => 1,
                'agreements' => [
                    [
                        'content' => $content,
                        'checkboxText' => $checkboxText,
                        'mode' => $mode,
                        'agreementId' => $agreementId
                    ]
                ]
            ]
        ];

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $agreement = $this->getMock(\Magento\CheckoutAgreements\Api\Data\AgreementInterface::class);
        $this->agreementsRepositoryMock->expects($this->any())->method('getList')->willReturn([$agreement]);

        $agreement->expects($this->once())->method('getIsHtml')->willReturn(true);
        $agreement->expects($this->once())->method('getContent')->willReturn($content);
        $agreement->expects($this->once())->method('getCheckboxText')->willReturn($checkboxText);
        $agreement->expects($this->once())->method('getMode')->willReturn($mode);
        $agreement->expects($this->once())->method('getAgreementId')->willReturn($agreementId);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    public function testGetConfigIfContentIsNotHtml()
    {
        $content = 'content';
        $escapedContent = 'escaped_content';
        $checkboxText = 'checkbox_text';
        $mode = \Magento\CheckoutAgreements\Model\AgreementModeOptions::MODE_AUTO;
        $agreementId = 100;
        $expectedResult = [
            'checkoutAgreements' => [
                'isEnabled' => 1,
                'agreements' => [
                    [
                        'content' => $escapedContent,
                        'checkboxText' => $checkboxText,
                        'mode' => $mode,
                        'agreementId' => $agreementId
                    ]
                ]
            ]
        ];

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $agreement = $this->getMock(\Magento\CheckoutAgreements\Api\Data\AgreementInterface::class);
        $this->agreementsRepositoryMock->expects($this->any())->method('getList')->willReturn([$agreement]);
        $this->escaperMock->expects($this->once())->method('escapeHtml')->with($content)->willReturn($escapedContent);

        $agreement->expects($this->once())->method('getIsHtml')->willReturn(false);
        $agreement->expects($this->once())->method('getContent')->willReturn($content);
        $agreement->expects($this->once())->method('getCheckboxText')->willReturn($checkboxText);
        $agreement->expects($this->once())->method('getMode')->willReturn($mode);
        $agreement->expects($this->once())->method('getAgreementId')->willReturn($agreementId);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }
}
