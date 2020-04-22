<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Rule;

use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\Rule\Job;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
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
            RuleProductProcessor::class,
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
            RuleProductProcessor::class,
            ['markIndexerAsInvalid']
        );
        $exceptionMessage = 'Test exception message';
        $exceptionCallback = function () use ($exceptionMessage) {
            throw new LocalizedException(__($exceptionMessage));
        };
        $ruleProcessorMock->expects($this->once())
            ->method('markIndexerAsInvalid')
            ->willReturnCallback($exceptionCallback);
        $jobModel = new Job($ruleProcessorMock);
        $jobModel->applyAll();
        $this->assertEquals($exceptionMessage, $jobModel->getError());
    }
}
