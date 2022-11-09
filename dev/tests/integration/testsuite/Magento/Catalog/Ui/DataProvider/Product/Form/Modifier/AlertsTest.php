<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Alerts modifier test
 *
 * @see \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Alerts
 *
 * @magentoAppArea adminhtml
 */
class AlertsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Alerts */
    private $stockAlertsModifier;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->stockAlertsModifier = $this->objectManager->get(Alerts::class);
    }

    /**
     * @magentoConfigFixture current_store catalog/productalert/allow_stock 1
     * @magentoConfigFixture current_store catalog/productalert/allow_price 1
     *
     * @return void
     */
    public function testModifyMeta(): void
    {
        $meta = $this->stockAlertsModifier->modifyMeta([]);
        $this->assertArrayHasKey('alerts', $meta);
        $stockContent = $meta['alerts']['children'][Alerts::DATA_SCOPE_STOCK]['arguments']['data']['config']['content'];
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath("//div[@data-grid-id='alertStock']", $stockContent)
        );
        $priceContent = $meta['alerts']['children'][Alerts::DATA_SCOPE_PRICE]['arguments']['data']['config']['content'];
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath("//div[@data-grid-id='alertPrice']", $priceContent)
        );
    }
}
