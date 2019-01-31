<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model\Rule;

use Magento\CatalogRule\Model\Rule\Job;

class JobTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test for method applyAll
     *
     * Checks that invalidate Rule indexer
     *
     * @return void
     */
    public function testApplyAll()
    {
        $ruleProcessorMock = $this->createPartialMock(
            \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor::class,
            ['markIndexerAsInvalid']
        );
        $ruleProcessorMock->expects($this->once())->method('markIndexerAsInvalid');
        $jobModel = new Job($ruleProcessorMock);
        $jobModel->applyAll();
    }

    /**
     * @return void
     */
    public function testExceptionApplyAll()
    {
        $ruleProcessorMock = $this->createPartialMock(
            \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor::class,
            ['markIndexerAsInvalid']
        );
        $exceptionMessage = 'Test exception message';
        $exceptionCallback = function () use ($exceptionMessage) {
            throw new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage));
        };
        $ruleProcessorMock->expects($this->once())
            ->method('markIndexerAsInvalid')
            ->will($this->returnCallback($exceptionCallback));
        $jobModel = new Job($ruleProcessorMock);
        $jobModel->applyAll();
        $this->assertEquals($exceptionMessage, $jobModel->getError());
    }
}
