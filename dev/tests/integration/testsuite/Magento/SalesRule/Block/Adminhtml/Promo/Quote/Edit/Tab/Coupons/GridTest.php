<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons;

use Magento\Backend\Block\Widget\Grid\Massaction\Extended as MassActionBlock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\SalesRule\Model\RegistryConstants;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\SalesRule\Model\Rule;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class to test Coupon codes grid
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/SalesRule/_files/cart_rule_with_coupon_list.php
 * @see \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid
 */
class GridTest extends TestCase
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Rule
     */
    private $salesRule;

    /**
     * @var Registry
     */
    private $registry;

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
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);

        $this->initSalesRule();
        $this->prepareLayout();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->registry->unregister(RegistryConstants::CURRENT_SALES_RULE);
    }

    /**
     * Check if mass action block exists
     */
    public function testMassActionBlockExists()
    {
        $this->assertNotFalse(
            $this->getMassActionBlock(),
            'Mass action block does not exist in the grid, or it name was changed.'
        );
    }

    /**
     * Check if function returns correct result
     */
    public function testMassActionBlockContainsCorrectIdList()
    {
        $this->assertEquals(
            implode(',', $this->getCouponsIdList()),
            $this->getMassActionBlock()->getGridIdsJson(),
            'Function returns incorrect result.'
        );
    }

    /**
     * Retrieve mass action block
     *
     * @return bool|MassActionBlock
     */
    private function getMassActionBlock()
    {
        /** @var Grid $grid */
        $grid = $this->layout->getBlock('sales_rule_quote_edit_tab_coupons_grid');

        return $grid->getMassactionBlock();
    }

    /**
     * Prepare layout blocks
     */
    private function prepareLayout()
    {
        $this->layout = Bootstrap::getObjectManager()->create(LayoutInterface::class);
        $this->layout->getUpdate()->load('sales_rule_promo_quote_couponsgrid');
        $this->layout->generateXml();
        $this->layout->generateElements();

        $grid = $this->layout->getBlock('sales_rule_quote_edit_tab_coupons_grid');
        $grid->toHtml();
    }

    /**
     * Init current sales rule
     */
    private function initSalesRule()
    {
        /** @var RuleCollection $collection */
        $collection = Bootstrap::getObjectManager()->create(RuleCollection::class);
        $collection->addFieldToFilter('name', 'Rule with coupon list');
        $this->salesRule = $collection->getFirstItem();
        $this->registry->register(RegistryConstants::CURRENT_SALES_RULE, $this->salesRule);
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
}
