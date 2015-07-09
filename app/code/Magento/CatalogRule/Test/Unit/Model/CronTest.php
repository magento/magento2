<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CronTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\CatalogRule\Model\Cron
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
            'Magento\CatalogRule\Model\Cron',
            [
                'ruleProductProcessor' => $this->ruleProductProcessor,
            ]
        );
    }

    public function testDailyCatalogUpdate()
    {
        $this->ruleProductProcessor->expects($this->once())->method('markIndexerAsInvalid');

        $this->cron->dailyCatalogUpdate();
    }
}
