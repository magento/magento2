<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AlertsTest extends TestCase
{
    /**
     * @var Alerts
     */
    protected $alerts;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->alerts = $helper->getObject(
            Alerts::class,
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
                ScopeInterface::SCOPE_STORE,
                null,
                $priceAllow,
            ],
            [
                'catalog/productalert/allow_stock',
                ScopeInterface::SCOPE_STORE,
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
    public static function canShowTabDataProvider()
    {
        return [
            'alert_price_and_stock_allow' => [true, true, true],
            'alert_price_is_allowed_and_stock_is_unallowed' => [true, false, true],
            'alert_price_is_unallowed_and_stock_is_allowed' => [false, true, true],
            'alert_price_is_unallowed_and_stock_is_unallowed' => [false, false, false]
        ];
    }
}
