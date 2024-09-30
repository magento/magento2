<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote\ExportCoupons;

use Magento\Framework\App\ResourceConnection;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\SalesRule\Model\Rule;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test export coupon xml
 *
 * Verify export xml
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/SalesRule/_files/cart_rule_with_coupon_list.php
 */
class ExportCouponsXmlTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $uri = 'backend/sales_rule/promo_quote/exportCouponsXml';

    /**
     * @var string
     */
    protected $resource = 'Magento_SalesRule::quote';

    /**
     * @var Rule
     */
    private $salesRule;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $this->initSalesRule();
    }

    /**
     * Prepare request
     *
     * @return void
     */
    private function prepareRequest(): void
    {
        $couponList = $this->getCouponsIdList();
        if (count($couponList)) {
            $this->getRequest()->setParams(['internal_ids' => $couponList[0]])->setMethod('POST');
        }
    }

    /**
     * Init current sales rule
     *
     * @return void
     */
    private function initSalesRule(): void
    {
        /** @var RuleCollection $collection */
        $collection = Bootstrap::getObjectManager()->create(RuleCollection::class);
        $collection->addFieldToFilter('name', 'Rule with coupon list');
        $this->salesRule = $collection->getFirstItem();
    }

    /**
     * Retrieve id list of coupons
     *
     * @return array
     */
    private function getCouponsIdList(): array
    {
        $select = $this->resourceConnection->getConnection()
            ->select()
            ->from($this->resourceConnection->getTableName('salesrule_coupon'))
            ->columns(['coupon_id'])
            ->where('rule_id=?', $this->salesRule->getId());

        return $this->resourceConnection->getConnection()->fetchCol($select);
    }

    /**
     * Test export xml
     *
     * @return void
     */
    public function testExportCsv(): void
    {
        $this->prepareRequest();
        $this->dispatch($this->uri);
        $this->assertStringNotContainsString('404 Error', $this->getResponse()->getBody());
    }
}
