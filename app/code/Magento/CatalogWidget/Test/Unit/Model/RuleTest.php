<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogWidget\Test\Unit\Model;

use Magento\CatalogWidget\Model\Rule;
use Magento\CatalogWidget\Model\Rule\Condition\Combine;
use Magento\CatalogWidget\Model\Rule\Condition\CombineFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var CombineFactory|MockObject
     */
    protected $combineFactory;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->combineFactory = $this->createMock(CombineFactory::class);

        $this->rule = $this->objectManager->getObject(
            Rule::class,
            [
                'conditionsFactory' => $this->combineFactory
            ]
        );
    }

    public function testGetConditionsInstance()
    {
        $condition = $this->createMock(Combine::class);
        $this->combineFactory->expects($this->once())->method('create')->willReturn($condition);
        $this->assertSame($condition, $this->rule->getConditionsInstance());
    }

    public function testGetActionsInstance()
    {
        $this->assertNull($this->rule->getActionsInstance());
    }
}
