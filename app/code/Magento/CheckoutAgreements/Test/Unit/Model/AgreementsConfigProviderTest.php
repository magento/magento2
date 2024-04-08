<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;
use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use Magento\CheckoutAgreements\Model\AgreementModeOptions;
use Magento\CheckoutAgreements\Model\AgreementsConfigProvider;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AgreementsConfigProvider.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AgreementsConfigProviderTest extends TestCase
{
    /**
     * @var AgreementsConfigProvider
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $escaperMock;

    /**
     * @var MockObject
     */
    private $checkoutAgreementsListMock;

    /**
     * @var MockObject
     */
    private $agreementsFilterMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $agreementsRepositoryMock = $this->createMock(
            CheckoutAgreementsRepositoryInterface::class
        );
        $this->escaperMock = $this->createMock(Escaper::class);

        $this->checkoutAgreementsListMock = $this->createMock(
            CheckoutAgreementsListInterface::class
        );
        $this->agreementsFilterMock = $this->createMock(
            ActiveStoreAgreementsFilter::class
        );

        $this->model = new AgreementsConfigProvider(
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
    public function testGetConfigIfContentIsHtml(): void
    {
        $content = 'content';
        $checkboxText = 'checkbox_text';
        $escapedCheckboxText = 'escaped_checkbox_text';
        $mode = AgreementModeOptions::MODE_AUTO;
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
                    ]
                ]
            ]
        ];

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $agreement = $this->getMockForAbstractClass(AgreementInterface::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
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
    public function testGetConfigIfContentIsNotHtml(): void
    {
        $content = 'content';
        $escapedContent = 'escaped_content';
        $checkboxText = 'checkbox_text';
        $escapedCheckboxText = 'escaped_checkbox_text';
        $mode = AgreementModeOptions::MODE_AUTO;
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
                    ]
                ]
            ]
        ];

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $agreement = $this->getMockForAbstractClass(AgreementInterface::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->agreementsFilterMock->expects($this->once())
            ->method('buildSearchCriteria')
            ->willReturn($searchCriteriaMock);
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn([$agreement]);

        $this->escaperMock
            ->method('escapeHtml')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$content] => $escapedContent,
                [$checkboxText] => $escapedCheckboxText
            });
        $agreement->expects($this->once())->method('getIsHtml')->willReturn(false);
        $agreement->expects($this->once())->method('getContent')->willReturn($content);
        $agreement->expects($this->once())->method('getCheckboxText')->willReturn($checkboxText);
        $agreement->expects($this->once())->method('getMode')->willReturn($mode);
        $agreement->expects($this->once())->method('getAgreementId')->willReturn($agreementId);
        $agreement->expects($this->once())->method('getContentHeight')->willReturn($contentHeight);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }
}
