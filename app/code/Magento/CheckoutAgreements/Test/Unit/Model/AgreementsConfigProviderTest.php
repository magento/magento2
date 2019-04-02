<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Model\AgreementsProvider;

/**
 * Tests for AgreementsConfigProvider.
 */
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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->agreementsRepositoryMock = $this->getMock(
            '\Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface',
            [],
            [],
            '',
            false
        );
        $this->escaperMock = $this->getMock('\Magento\Framework\Escaper', [], [], '', false);

        $this->model = new \Magento\CheckoutAgreements\Model\AgreementsConfigProvider(
            $this->scopeConfigMock,
            $this->agreementsRepositoryMock,
            $this->escaperMock
        );
    }

    /**
     * Test for getConfig if content is HTML.
     *
     * @return void
     */
    public function testGetConfigIfContentIsHtml()
    {
        $content = 'content';
        $checkboxText = 'checkbox_text';
        $escapedCheckboxText = 'escaped_checkbox_text';
        $mode = \Magento\CheckoutAgreements\Model\AgreementModeOptions::MODE_AUTO;
        $agreementId = 100;
        $expectedResult = [
            'checkoutAgreements' => [
                'isEnabled' => 1,
                'agreements' => [
                    [
                        'content' => $content,
                        'checkboxText' => $escapedCheckboxText,
                        'mode' => $mode,
                        'agreementId' => $agreementId,
                    ],
                ],
            ],
        ];

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $agreement = $this->getMock('\Magento\CheckoutAgreements\Api\Data\AgreementInterface');
        $this->agreementsRepositoryMock->expects($this->once())->method('getList')->willReturn([$agreement]);

        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with($checkboxText)
            ->willReturn($escapedCheckboxText);
        $agreement->expects($this->once())->method('getIsHtml')->willReturn(true);
        $agreement->expects($this->once())->method('getContent')->willReturn($content);
        $agreement->expects($this->once())->method('getCheckboxText')->willReturn($checkboxText);
        $agreement->expects($this->once())->method('getMode')->willReturn($mode);
        $agreement->expects($this->once())->method('getAgreementId')->willReturn($agreementId);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    /**
     * Test for getConfig if content is not HTML.
     *
     * @return void
     */
    public function testGetConfigIfContentIsNotHtml()
    {
        $content = 'content';
        $escapedContent = 'escaped_content';
        $checkboxText = 'checkbox_text';
        $escapedCheckboxText = 'escaped_checkbox_text';
        $mode = \Magento\CheckoutAgreements\Model\AgreementModeOptions::MODE_AUTO;
        $agreementId = 100;
        $expectedResult = [
            'checkoutAgreements' => [
                'isEnabled' => 1,
                'agreements' => [
                    [
                        'content' => $escapedContent,
                        'checkboxText' => $escapedCheckboxText,
                        'mode' => $mode,
                        'agreementId' => $agreementId,
                    ],
                ],
            ],
        ];

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $agreement = $this->getMock('\Magento\CheckoutAgreements\Api\Data\AgreementInterface');
        $this->agreementsRepositoryMock->expects($this->once())->method('getList')->willReturn([$agreement]);

        $this->escaperMock->expects($this->at(0))->method('escapeHtml')->with($content)->willReturn($escapedContent);
        $this->escaperMock->expects($this->at(1))
            ->method('escapeHtml')
            ->with($checkboxText)
            ->willReturn($escapedCheckboxText);
        $agreement->expects($this->once())->method('getIsHtml')->willReturn(false);
        $agreement->expects($this->once())->method('getContent')->willReturn($content);
        $agreement->expects($this->once())->method('getCheckboxText')->willReturn($checkboxText);
        $agreement->expects($this->once())->method('getMode')->willReturn($mode);
        $agreement->expects($this->once())->method('getAgreementId')->willReturn($agreementId);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }
}
