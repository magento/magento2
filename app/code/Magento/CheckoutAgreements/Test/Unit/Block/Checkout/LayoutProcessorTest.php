<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Block\Checkout;

class LayoutProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Block\Checkout\LayoutProcessor
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $agreementsRepositoryMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->escaperMock = $this->getMock('\Magento\Framework\Escaper', [], [], '', false);
        $this->agreementsRepositoryMock = $this->getMock(
            '\Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface'
        );

        $this->model = $objectManager->getObject(
            'Magento\CheckoutAgreements\Block\Checkout\LayoutProcessor',
            [
                'escaper' => $this->escaperMock,
                'checkoutAgreementsRepository' => $this->agreementsRepositoryMock
            ]
        );
    }

    public function testProcessIfAgreementWithHtml()
    {
        $agreementData = [
            'content' => 'content',
            'height' => '100',
            'checkboxText' => 'checkbox_text'
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
        ['children']['payments-list']['children']['before-place-order']['children']['checkout-agreements-modal']
        ['config']['agreementConfiguration'] = null;

        $expectedResult = $jsLayout;
        $expectedResult['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children']['before-place-order']['children']
        ['checkout-agreements-modal']['config']['agreementConfiguration'][] = $agreementData;

        $agreementMock = $this->getMock('\Magento\CheckoutAgreements\Api\Data\AgreementInterface');
        $this->agreementsRepositoryMock->expects($this->once())->method('getList')->willReturn([$agreementMock]);

        $agreementMock->expects($this->once())->method('getIsHtml')->willReturn(true);
        $agreementMock->expects($this->once())->method('getContent')->willReturn($agreementData['content']);
        $agreementMock->expects($this->once())->method('getContentHeight')->willReturn($agreementData['height']);
        $agreementMock->expects($this->once())->method('getCheckboxText')->willReturn($agreementData['checkboxText']);

        $this->assertEquals($expectedResult, $this->model->process($jsLayout));
    }

    public function testProcessIfAgreementRichText()
    {
        $agreementData = [
            'content' => 'content',
            'height' => '100',
            'checkboxText' => 'checkbox_text'
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
        ['children']['payments-list']['children']['before-place-order']['children']['checkout-agreements-modal']
        ['config']['agreementConfiguration'] = null;

        $expectedResult = $jsLayout;
        $expectedResult['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children']['before-place-order']['children']
        ['checkout-agreements-modal']['config']['agreementConfiguration'][] = $agreementData;

        $agreementMock = $this->getMock('\Magento\CheckoutAgreements\Api\Data\AgreementInterface');
        $this->agreementsRepositoryMock->expects($this->once())->method('getList')->willReturn([$agreementMock]);

        $this->escaperMock->expects($this->once())->method('escapeHtml')
            ->with($agreementData['content'])
            ->willReturn($agreementData['content']);

        $agreementMock->expects($this->once())->method('getIsHtml')->willReturn(false);
        $agreementMock->expects($this->once())->method('getContent')->willReturn($agreementData['content']);
        $agreementMock->expects($this->once())->method('getContentHeight')->willReturn($agreementData['height']);
        $agreementMock->expects($this->once())->method('getCheckboxText')->willReturn($agreementData['checkboxText']);

        $this->assertEquals($expectedResult, $this->model->process($jsLayout));
    }
}
