<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Store\Model\ScopeInterface;

class AgreementsConfigProviderTest extends \PHPUnit\Framework\TestCase
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
    protected $escaperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutAgreementsListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $agreementsFilterMock;

    protected function setUp()
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

        $agreement = $this->createMock(\Magento\CheckoutAgreements\Api\Data\AgreementInterface::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->agreementsFilterMock->expects($this->once())
            ->method('buildSearchCriteria')
            ->willReturn($searchCriteriaMock);
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn([$agreement]);

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

        $agreement = $this->createMock(\Magento\CheckoutAgreements\Api\Data\AgreementInterface::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->agreementsFilterMock->expects($this->once())
            ->method('buildSearchCriteria')
            ->willReturn($searchCriteriaMock);
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn([$agreement]);

        $this->escaperMock->expects($this->once())->method('escapeHtml')->with($content)->willReturn($escapedContent);

        $agreement->expects($this->once())->method('getIsHtml')->willReturn(false);
        $agreement->expects($this->once())->method('getContent')->willReturn($content);
        $agreement->expects($this->once())->method('getCheckboxText')->willReturn($checkboxText);
        $agreement->expects($this->once())->method('getMode')->willReturn($mode);
        $agreement->expects($this->once())->method('getAgreementId')->willReturn($agreementId);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }
}
