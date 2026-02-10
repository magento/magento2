<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Spi\RuleQuoteRecollectTotalsInterface;
use Magento\SalesRule\Observer\RuleQuoteRecollectTotalsObserver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleQuoteRecollectTotalsObserverTest extends TestCase
{
    /**
     * @var RuleQuoteRecollectTotalsInterface|MockObject
     */
    private $ruleQuoteRecollectTotals;

    /**
     * @var RuleQuoteRecollectTotalsObserver
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->ruleQuoteRecollectTotals = $this->createMock(RuleQuoteRecollectTotalsInterface::class);
        $this->model = new RuleQuoteRecollectTotalsObserver($this->ruleQuoteRecollectTotals);
    }

    /**
     * @param array $origData
     * @param array $data
     * @param bool $isDeleted
     * @param bool $recollect
     * @return void
     */
    #[DataProvider('executeDataProvider')]
    public function testExecute(
        array $origData,
        array $data,
        bool $isDeleted,
        bool $recollect
    ): void {
        $this->ruleQuoteRecollectTotals->expects($recollect ? $this->once() : $this->never())
            ->method('execute');
        $objectManager = new ObjectManager($this);
        $rule = $objectManager->getObject(Rule::class);
        $id = $data['id'] ?? 1;
        unset($data['id']);
        $rule->isDeleted($isDeleted);
        $rule->setData($origData);
        $rule->setOrigData();
        $rule->setData($data);
        $rule->setId($id);
        $observer = new Observer(['rule' => $rule]);
        $this->model->execute($observer);
    }

    /**
     * @return array[]
     */
    public static function executeDataProvider(): array
    {
        return [
            [[], ['id' => null], false, false],
            [[], [], false, false],
            [[], [], true, true],
            [[], ['is_active' => false], false, false],
            [[], ['is_active' => true], false, false],
            [['is_active' => false], ['is_active' => false], false, false],
            [['is_active' => false], ['is_active' => true], false, false],
            [['is_active' => true], ['is_active' => false], false, true],
            [['is_active' => true], ['is_active' => true], false, false],
        ];
    }
}
