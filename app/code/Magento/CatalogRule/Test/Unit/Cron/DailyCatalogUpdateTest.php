<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Cron;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DailyCatalogUpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Processor
     *
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleProductProcessor;

    /**
     * Cron object
     *
     * @var \Magento\CatalogRule\Cron\DailyCatalogUpdate
     */
    protected $cron;

    protected function setUp()
    {
        $this->ruleProductProcessor = $this->getMock(
            'Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor',
            [],
            [],
            '',
            false
        );

        $this->cron = (new ObjectManager($this))->getObject(
            'Magento\CatalogRule\Cron\DailyCatalogUpdate',
            [
                'ruleProductProcessor' => $this->ruleProductProcessor,
            ]
        );
    }

    public function testDailyCatalogUpdate()
    {
        $this->ruleProductProcessor->expects($this->once())->method('markIndexerAsInvalid');

        $this->cron->execute();
    }
}
