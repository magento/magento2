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
    private $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

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
        $this->filterBuilderMock = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->searchCriteriaBuilderMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->model = new \Magento\CheckoutAgreements\Model\AgreementsConfigProvider(
            $this->scopeConfigMock,
            $agreementsRepositoryMock,
            $this->escaperMock,
            $this->checkoutAgreementsListMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock,
            $this->storeManagerMock
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
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($this->buildSearchCriteria())
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
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($this->buildSearchCriteria())
            ->willReturn([$agreement]);

        $this->escaperMock->expects($this->once())->method('escapeHtml')->with($content)->willReturn($escapedContent);

        $agreement->expects($this->once())->method('getIsHtml')->willReturn(false);
        $agreement->expects($this->once())->method('getContent')->willReturn($content);
        $agreement->expects($this->once())->method('getCheckboxText')->willReturn($checkboxText);
        $agreement->expects($this->once())->method('getMode')->willReturn($mode);
        $agreement->expects($this->once())->method('getAgreementId')->willReturn($agreementId);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    /**
     * Build mock object for search criteria
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildSearchCriteria() : \PHPUnit_Framework_MockObject_MockObject
    {
        $storeId = 1;
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $storeFilterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $activeFilterMock = $this->createMock(\Magento\Framework\Api\Filter::class);

        $this->filterBuilderMock->expects($this->at(0))->method('setField')->with('store_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(1))->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(2))->method('setValue')->with($storeId)->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(3))->method('create')->willReturn($storeFilterMock);
        $this->filterBuilderMock->expects($this->at(4))->method('setField')->with('is_active')->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(5))->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(6))->method('setValue')->with(1)->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(7))->method('create')->willReturn($activeFilterMock);

        $this->searchCriteriaBuilderMock->expects($this->at(0))
            ->method('addFilters')
            ->with([$storeFilterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->at(1))
            ->method('addFilters')
            ->with([$activeFilterMock])
            ->willReturnSelf();

        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->at(2))->method('create')->willReturn($searchCriteriaMock);
        return $searchCriteriaMock;
    }
}
