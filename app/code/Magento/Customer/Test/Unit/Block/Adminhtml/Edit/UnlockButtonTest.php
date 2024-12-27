<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Block\Adminhtml\Edit\UnlockButton;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;

class UnlockButtonTest extends TestCase
{
    /**
     * @var CustomerRegistry
     */
    protected $customerRegistryMock;

    /**
     * @var  Context
     */
    protected $contextMock;

    /**
     * @var Customer
     */
    protected $customerModelMock;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilderMock;

    /**
     * @var Registry
     */
    protected $registryMock;

    /**
     * @var UnlockButton
     */
    protected $block;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->customerRegistryMock = $this->createPartialMock(
            CustomerRegistry::class,
            ['retrieve']
        );
        $this->customerModelMock = $this->createMock(Customer::class);
        $this->registryMock = $this->createPartialMock(Registry::class, ['registry']);

        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->onlyMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->block = $objectManagerHelper->getObject(
            UnlockButton::class,
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
    public static function getButtonDataProvider()
    {
        return [
            [
                'result' => [
                    'label' => new Phrase('Unlock'),
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
