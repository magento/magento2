<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\MessageInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test class for \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Save
 *
 * @magentoAppArea adminhtml
 */
class SaveTest extends AbstractBackendController
{
    public function testCreateRuleWithOnlyFormkey(): void
    {
        $requestData = [];
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);

        $this->dispatch('backend/sales_rule/promo_quote/save');
        $this->assertSessionMessages(
            self::equalTo(['You saved the rule.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    public function testCreateRuleWithFreeShipping(): void
    {
        $ruleCollection = Bootstrap::getObjectManager()->create(Collection::class);
        $resource = $ruleCollection->getResource();
        $select = $resource->getConnection()->select();
        $select->from($resource->getTable('salesrule'), [new \Zend_Db_Expr('MAX(rule_id)')]);
        $maxId = (int)$resource->getConnection()->fetchOne($select);

        $requestData = [
            'simple_free_shipping' => 1,
        ];
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);

        $this->dispatch('backend/sales_rule/promo_quote/save');
        $this->assertSessionMessages(
            self::equalTo(['You saved the rule.']),
            MessageInterface::TYPE_SUCCESS
        );

        $select = $resource->getConnection()->select();
        $select
            ->from($resource->getTable('salesrule'), ['simple_free_shipping'])
            ->where('rule_id > ?', $maxId);
        $simpleFreeShipping = (int)$resource->getConnection()->fetchOne($select);

        $this->assertEquals(1, $simpleFreeShipping);
    }

    public function testCreateRuleWithWrongDates(): void
    {
        $requestData = [
            'from_date' => '2023-02-02',
            'to_date' => '2023-01-01',
        ];
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);

        $this->dispatch('backend/sales_rule/promo_quote/save');
        $this->assertSessionMessages(
            self::equalTo(['End Date must follow Start Date.']),
            MessageInterface::TYPE_ERROR
        );
    }
}
