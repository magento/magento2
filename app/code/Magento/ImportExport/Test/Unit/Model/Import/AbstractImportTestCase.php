<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Import;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractImportTestCase extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @param array|null $methods
     * @return ProcessingErrorAggregatorInterface|MockObject
     */
    protected function getErrorAggregatorObject(array $methods = [])
    {
        $errorFactory = $this->getMockBuilder(
            ProcessingErrorFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $errorFactory->method('create')->willReturn(
            $this->objectManagerHelper->getObject(
                ProcessingError::class
            )
        );
        return $this->getMockBuilder(
            ProcessingErrorAggregator::class
        )->onlyMethods($methods)
            ->setConstructorArgs(['errorFactory' => $errorFactory])
            ->getMock();
    }
}
