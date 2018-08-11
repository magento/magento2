<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Rate;

use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Rate\Provider;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class ProviderTest provides coverage
 * of Tax Rate model options provider.
 */
class ProviderTest extends \Magento\TestFramework\Indexer\TestCase
{
    public static function setUpBeforeClass()
    {
        $db = Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();

        parent::setUpBeforeClass();
    }

    /**
     * Test of requesting tax rates by search criteria.
     * @magentoDbIsolation disabled
     */
    public function testToOptionArray()
    {
        $objectManager = Bootstrap::getObjectManager();
        $optionsCount = 1;

        /** @var Collection $collection */
        $collection = $objectManager->get(Collection::class);
        $expectedResult = [];

        /** @var $taxRate Rate */
        foreach ($collection as $taxRate) {
            $expectedResult[] = ['value' => $taxRate->getId(), 'label' => $taxRate->getCode()];
            if (count($expectedResult) >= $optionsCount) {
                break;
            }
        }

        /** @var Source $source */
        if (empty($expectedResult)) {
            $this->fail('Preconditions failed: At least one tax rate should be available.');
        }

        $provider = $objectManager->get(Provider::class);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder =  $objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->setPageSize($optionsCount);
        $searchCriteriaBuilder->setCurrentPage(1);

        $searchCriteria = $searchCriteriaBuilder->create();

        $this->assertEquals(
            $expectedResult,
            $provider->toOptionArray($searchCriteria),
            'Tax rate options are invalid.'
        );
    }

    /**
     * teardown
     */
    public function tearDown()
    {
        parent::tearDown();
    }
}
