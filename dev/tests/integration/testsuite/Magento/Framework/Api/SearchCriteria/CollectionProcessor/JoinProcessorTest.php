<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class JoinProcessorTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test proper application of the join processor for consecutive calls with different collections.
     *
     * @return void
     */
    public function testMultipleCollections(): void
    {
        $customJoinMock = $this->getMockBuilder(CustomJoinInterface::class)
            ->getMock();

        $customJoinMock->expects($this->exactly(2))
            ->method('apply');

        $joinProcessor = $this->objectManager->create(JoinProcessor::class, [
            'customJoins' => [
                'test_join' => $customJoinMock,
            ],
            'fieldMapping' => [
                'test_field' => 'test_join',
            ],
        ]);

        $searchCriteria = $this->objectManager->create(SearchCriteriaBuilder::class)
            ->addFilter('test_field', 'test')
            ->create();

        $collection1 = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collection2 = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $joinProcessor->process($searchCriteria, $collection1);
        $joinProcessor->process($searchCriteria, $collection2);
    }
}
