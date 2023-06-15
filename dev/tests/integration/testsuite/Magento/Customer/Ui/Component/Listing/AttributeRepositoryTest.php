<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Listing;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Customer\Ui\Component\Listing\AttributeRepository.
 *
 * @magentoAppArea adminhtml
 */
class AttributeRepositoryTest extends TestCase
{
    /**
     * @var AttributeRepository
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->create(AttributeRepository::class);
    }

    /**
     * Test for get store_id option array
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @return void
     */
    public function testGetOptionArray(): void
    {
        $result = $this->model->getMetadataByCode('store_id');

        $this->assertTrue(isset($result['options']['1']['value']));
        $this->assertEquals(
            ['Default Store View', 'Fixture Store'],
            $this->getStoreViewLabels($result['options'][1]['value'])
        );
    }

    /**
     * Returns prepared store view labels
     *
     * @param array $storeViewsData
     * @return array
     */
    private function getStoreViewLabels(array $storeViewsData): array
    {
        $result = [];
        foreach ($storeViewsData as $storeView) {
            $result[] = str_replace("\xc2\xa0", '', $storeView['label']);
        }

        return $result;
    }
}
