<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\ResourceConnection;

/**
 * Test Class for \Magento\Framework\Mview\View\Changelog
 *
 * @magentoDbIsolation disabled
 */
class EnhancedMviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->resourceConnection = $this->objectManager->get(ResourceConnection::class);
        parent::setUp();
    }

    /**
     * @dataProvider attributesDataProvider
     * @magentoDataFixture Magento/Eav/_files/enable_mview_for_test_view.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @param array $expectedAttributes
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testCreateProduct(array $expectedAttributes)
    {
        $changelogData = $this->getChangelogData();
        $attributesMapping = $this->getAttributeCodes();
        $attributes = [];
        foreach ($changelogData as $row) {
            $this->assertArrayHasKey('store_id', $row);
            $this->assertArrayHasKey('attribute_id', $row);

            if ($row['store_id'] == 0 && $row['attribute_id'] !== null) {
                $attributes[$attributesMapping[$row['attribute_id']]] = $attributesMapping[$row['attribute_id']];
            }
        }
        sort($expectedAttributes);
        sort($attributes);
        $this->assertEquals($attributes, $expectedAttributes);

        $this->assertTrue(true);
    }

    /**
     * @return array
     */
    private function getAttributeCodes(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('eav_attribute'), ['attribute_id', 'attribute_code']);
        return $connection->fetchPairs($select);
    }

    /**
     * @return array|\string[][]
     */
    public function attributesDataProvider(): array
    {
        return [
            [
                'default' => [
                    'name',
                    'meta_title',
                    'meta_description',
                    'is_returnable',
                    'options_container'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getChangelogData()
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('test_view_cl'));
        return $connection->fetchAll($select);
    }
}
