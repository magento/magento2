<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class UnlockButtonTest
 * @package Magento\Customer\Block\Adminhtml\Edit
 */
class UnlockButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistryMock;

    /**
     * AccountManagement Helper
     *
     * @var \Magento\Customer\Helper\AccountManagement
     */
    protected $accountManagementHelperMock;

    /**
     * @var  \Magento\Backend\Block\Widget\Context
     */
    protected $contextMock;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModelMock;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Customer\Block\Adminhtml\Edit\UnlockButton
     */
    protected $block;

    protected function setUp()
    {
        $this->contextMock = $this->getMock(
            'Magento\Backend\Block\Widget\Context',
            [],
            [],
            '',
            false
        );
        $this->customerRegistryMock = $this->getMock(
            'Magento\Customer\Model\CustomerRegistry',
            ['retrieve'],
            [],
            '',
            false
        );
        $this->customerModelMock = $this->getMock(
            'Magento\Customer\Model\Customer',
            [],
            [],
            '',
            false
        );
        $this->accountManagementHelperMock = $this->getMock(
            'Magento\Customer\Helper\AccountManagement',
            ['isCustomerLocked'],
            [],
            '',
            false
        );
        $this->urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->block = $objectManagerHelper->getObject(
            'Magento\Customer\Block\Adminhtml\Edit\UnlockButton',
            [
                'context' => $this->contextMock,
                'customerRegistry' => $this->customerRegistryMock,
                'accountManagementHelper' => $this->accountManagementHelperMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
    }


    /**
     * @param array $result
     * @param bool $expectedValue
     * @dataProvider getButtonDataProvider
     */
    public function testGetButtonData($result, $expectedValue)
    {
        $this->customerRegistryMock->expects($this->once())->method('retrieve')->willReturn($this->customerModelMock);
        $this->accountManagementHelperMock->expects($this->once())->method('isCustomerLocked')->willReturn($expectedValue);
        $this->urlBuilderMock->expects($this->any())->method('getUrl')->willReturn('http://website.com/');

        $this->assertEquals($result, $this->block->getButtonData());
    }

    /**
     * @return array
     */
    public function getButtonDataProvider()
    {
        return [
            [
                'result' =>
                    [
                        'label' => new \Magento\Framework\Phrase('Unlock'),
                        'class' => 'unlock unlock-customer',
                        'on_click' => "location.href = 'http://website.com/';",
                        'sort_order' => 50,
                    ],
                'expectedValue' => 'true'
            ],
            ['result' => [], 'expectedValue' => false]
        ];
    }
}
