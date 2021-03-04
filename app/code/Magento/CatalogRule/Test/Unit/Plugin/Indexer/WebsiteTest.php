<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class WebsiteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Indexer processor mock
     *
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $ruleProductProcessor;

    /**
     * Website mock
     *
     * @var \Magento\Store\Model\Website|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subject;

    /**
     * Tested plugin
     *
     * @var \Magento\CatalogRule\Plugin\Indexer\Website
     */
    protected $plugin;

    protected function setUp(): void
    {
        $this->ruleProductProcessor = $this->createMock(
            \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor::class
        );
        $this->subject = $this->createMock(\Magento\Store\Model\Website::class);

        $this->plugin = (new ObjectManager($this))->getObject(
            \Magento\CatalogRule\Plugin\Indexer\Website::class,
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
