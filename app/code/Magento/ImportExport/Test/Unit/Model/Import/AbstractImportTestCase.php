<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Import;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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
     * @return \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface
     */
    protected function getErrorAggregatorObject()
    {
        $errorFactory = $this->getMockBuilder(
            'Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $errorFactory->method('create')->willReturn(
            $this->objectManagerHelper->getObject('Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError')
        );
        return $this->objectManagerHelper->getObject(
            'Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator',
            [
                'errorFactory' => $errorFactory
            ]
        );
    }
}
