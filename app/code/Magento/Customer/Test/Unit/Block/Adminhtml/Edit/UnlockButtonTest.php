<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \Magento\Framework\Registry
     */
    protected $registryMock;

    /**
     * @var \Magento\Customer\Block\Adminhtml\Edit\UnlockButton
     */
    protected $block;

    protected function setUp()
    {
        $this->contextMock = $this->getMock(
            \Magento\Backend\Block\Widget\Context::class,
            [],
            [],
            '',
            false
        );
        $this->customerRegistryMock = $this->getMock(
            \Magento\Customer\Model\CustomerRegistry::class,
            ['retrieve'],
            [],
            '',
            false
        );
        $this->customerModelMock = $this->getMock(
            \Magento\Customer\Model\Customer::class,
            [],
            [],
            '',
            false
        );
        $this->registryMock = $this->getMock(
            \Magento\Framework\Registry::class,
            ['registry'],
            [],
            '',
            false
        );

        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->block = $objectManagerHelper->getObject(
            \Magento\Customer\Block\Adminhtml\Edit\UnlockButton::class,
            [
                'context' => $this->contextMock,
                'customerRegistry' => $this->customerRegistryMock,
                'urlBuilder' => $this->urlBuilderMock,
                'registry' => $this->registryMock
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
        $this->registryMock->expects($this->any())->method('registry')->willReturn(1);
        $this->customerRegistryMock->expects($this->once())->method('retrieve')->willReturn($this->customerModelMock);
        $this->customerModelMock->expects($this->once())->method('isCustomerLocked')->willReturn($expectedValue);
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
                'result' => [
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
