<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Store\Model\ScopeInterface;

/**
 * Tests for AgreementsConfigProvider.
 */
class AgreementsConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\AgreementsConfigProvider
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $escaperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutAgreementsListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $agreementsFilterMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $agreementsRepositoryMock = $this->createMock(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface::class
        );
        $this->escaperMock = $this->createMock(\Magento\Framework\Escaper::class);

        $this->checkoutAgreementsListMock = $this->createMock(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface::class
        );
        $this->agreementsFilterMock = $this->createMock(
            \Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter::class
        );

        $this->model = new \Magento\CheckoutAgreements\Model\AgreementsConfigProvider(
            $this->scopeConfigMock,
            $agreementsRepositoryMock,
            $this->escaperMock,
            $this->checkoutAgreementsListMock,
            $this->agreementsFilterMock
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
        $contentHeight = '100px';
        $expectedResult = [
            'checkoutAgreements' => [
                'isEnabled' => 1,
                'agreements' => [
                    [
                        'content' => $content,
                        'checkboxText' => $escapedCheckboxText,
                        'mode' => $mode,
                        'agreementId' => $agreementId,
                        'contentHeight' => $contentHeight
                    ],
                ],
            ],
        ];

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $agreement = $this->createMock(\Magento\CheckoutAgreements\Api\Data\AgreementInterface::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->agreementsFilterMock->expects($this->once())
            ->method('buildSearchCriteria')
            ->willReturn($searchCriteriaMock);
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn([$agreement]);

        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with($checkboxText)
            ->willReturn($escapedCheckboxText);

        $agreement->expects($this->once())->method('getIsHtml')->willReturn(true);
        $agreement->expects($this->once())->method('getContent')->willReturn($content);
        $agreement->expects($this->once())->method('getCheckboxText')->willReturn($checkboxText);
        $agreement->expects($this->once())->method('getMode')->willReturn($mode);
        $agreement->expects($this->once())->method('getAgreementId')->willReturn($agreementId);
        $agreement->expects($this->once())->method('getContentHeight')->willReturn($contentHeight);

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
        $contentHeight = '100px';
        $expectedResult = [
            'checkoutAgreements' => [
                'isEnabled' => 1,
                'agreements' => [
                    [
                        'content' => $escapedContent,
                        'checkboxText' => $escapedCheckboxText,
                        'mode' => $mode,
                        'agreementId' => $agreementId,
                        'contentHeight' => $contentHeight
                    ],
                ],
            ],
        ];

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $agreement = $this->createMock(\Magento\CheckoutAgreements\Api\Data\AgreementInterface::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->agreementsFilterMock->expects($this->once())
            ->method('buildSearchCriteria')
            ->willReturn($searchCriteriaMock);
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn([$agreement]);

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
        $agreement->expects($this->once())->method('getContentHeight')->willReturn($contentHeight);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }
}
