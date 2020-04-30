<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer;

use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Plugin\Indexer\CustomerGroup;
use Magento\Customer\Model\Group;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerGroupTest extends TestCase
{
    /**
     * Rule processor mock
     *
     * @var RuleProductProcessor|MockObject
     */
    protected $ruleProductProcessor;

    /**
     * Subject group
     *
     * @var Group|MockObject
     */
    protected $subject;

    /**
     * Tested plugin
     *
     * @var CustomerGroup
     */
    protected $plugin;

    protected function setUp(): void
    {
        $this->ruleProductProcessor = $this->createMock(
            RuleProductProcessor::class
        );
        $this->subject = $this->createMock(Group::class);

        $this->plugin = (new ObjectManager($this))->getObject(
            CustomerGroup::class,
            [
                'ruleProductProcessor' => $this->ruleProductProcessor,
            ]
        );
    }

    public function testAfterDelete()
    {
        $this->ruleProductProcessor->expects($this->once())
            ->method('markIndexerAsInvalid');

        $this->assertEquals($this->subject, $this->plugin->afterDelete($this->subject, $this->subject));
    }
}
