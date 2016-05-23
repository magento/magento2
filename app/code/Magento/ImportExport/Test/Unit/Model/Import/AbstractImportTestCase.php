<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Import;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

abstract class AbstractImportTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @param array|null $methods
     * @return ProcessingErrorAggregatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getErrorAggregatorObject($methods = null)
    {
        $errorFactory = $this->getMockBuilder(
            'Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $errorFactory->method('create')->willReturn(
            $this->objectManagerHelper->getObject('Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError')
        );
        return $this->getMockBuilder('Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator')
            ->setMethods($methods)
            ->setConstructorArgs(['errorFactory' => $errorFactory])
            ->getMock();
    }
}
