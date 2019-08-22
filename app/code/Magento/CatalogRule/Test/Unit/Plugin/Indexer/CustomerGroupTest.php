<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CustomerGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Rule processor mock
     *
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleProductProcessor;

    /**
     * Subject group
     *
     * @var \Magento\Customer\Model\Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * Tested plugin
     *
     * @var \Magento\CatalogRule\Plugin\Indexer\CustomerGroup
     */
    protected $plugin;

    protected function setUp()
    {
        $this->ruleProductProcessor = $this->createMock(
            \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor::class
        );
        $this->subject = $this->createMock(\Magento\Customer\Model\Group::class);

        $this->plugin = (new ObjectManager($this))->getObject(
            \Magento\CatalogRule\Plugin\Indexer\CustomerGroup::class,
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
