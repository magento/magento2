<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab;

class AlertsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts
     */
    protected $alerts;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->alerts = $helper->getObject(
            \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    /**
     * @param bool $priceAllow
     * @param bool $stockAllow
     * @param bool $canShowTab
     *
     * @dataProvider canShowTabDataProvider
     */
    public function testCanShowTab($priceAllow, $stockAllow, $canShowTab)
    {
        $valueMap = [
            [
                'catalog/productalert/allow_price',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null,
                $priceAllow,
            ],
            [
                'catalog/productalert/allow_stock',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null,
                $stockAllow
            ],
        ];
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturnMap($valueMap);
        $this->assertEquals($canShowTab, $this->alerts->canShowTab());
    }

    /**
     * @return array
     */
    public function canShowTabDataProvider()
    {
        return [
            'alert_price_and_stock_allow' => [true, true, true],
            'alert_price_is_allowed_and_stock_is_unallowed' => [true, false, true],
            'alert_price_is_unallowed_and_stock_is_allowed' => [false, true, true],
            'alert_price_is_unallowed_and_stock_is_unallowed' => [false, false, false]
        ];
    }
}
