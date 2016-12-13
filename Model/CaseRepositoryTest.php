<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Testing case repository
 */
class CaseRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CaseRepository
     */
    private $repository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->filterBuilder = $this->objectManager->get(FilterBuilder::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->repository = $this->objectManager->get(CaseRepository::class);
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseRepository::delete
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     */
    public function testDelete()
    {
        $filters = [
            $this->filterBuilder->setField('case_id')
                ->setValue(123)
                ->create()
        ];
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)->create();
        $cases = $this->repository->getList($searchCriteria)
            ->getItems();

        $case = array_pop($cases);
        $this->repository->delete($case);

        static::assertEmpty($this->repository->getList($searchCriteria)->getItems());
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseRepository::getById
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     */
    public function testGetById()
    {
        $filters = [
            $this->filterBuilder->setField('case_id')
                ->setValue(123)
                ->create()
        ];
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)->create();
        $cases = $this->repository->getList($searchCriteria)
            ->getItems();

        $case = array_pop($cases);

        $found = $this->repository->getById($case->getEntityId());

        static::assertNotEmpty($found->getEntityId());
        static::assertEquals($case->getEntityId(), $found->getEntityId());
        static::assertEquals($case->getOrderId(), $found->getOrderId());
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseRepository::getList
     * @magentoDataFixture Magento/Signifyd/_files/multiple_cases.php
     */
    public function testGetListDateInterval()
    {
        $startDateInterval = [
            $this->filterBuilder->setField('created_at')
                ->setConditionType('gteq')
                ->setValue('2016-12-01 00:00:01')
                ->create()
        ];
        $endDateInterval = [
            $this->filterBuilder->setField('created_at')
                ->setConditionType('lteq')
                ->setValue('2016-12-03 23:59:59')
                ->create()
        ];

        $this->searchCriteriaBuilder->addFilters($startDateInterval);
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($endDateInterval)->create();
        $items = $this->repository->getList($searchCriteria)
            ->getItems();

        static::assertCount(3, $items);

        for ($i = 1; $i < 4; $i ++) {
            $current = array_shift($items);
            static::assertEquals($i, $current->getCaseId());
        }
    }

    /**
     * @covers \Magento\Signifyd\Model\CaseRepository::getList
     * @magentoDataFixture Magento/Signifyd/_files/multiple_cases.php
     */
    public function testGetListStatusProcessing()
    {
        $filters = [
            $this->filterBuilder->setField('status')
                ->setValue(CaseInterface::STATUS_PROCESSING)
                ->create()
        ];

        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)->create();
        $items = $this->repository->getList($searchCriteria)
            ->getItems();

        static::assertCount(1, $items);

        $case = array_pop($items);
        static::assertEquals(123, $case->getCaseId());
    }
}
