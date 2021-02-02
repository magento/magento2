<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesSequence\Test\Integration\Setup;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\SalesSequence\Model\Config as SequenceConfig;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\SalesSequence\Setup\SequenceCreator;
use Magento\SalesSequence\Model\ResourceModel\Meta as MetaResource;
use Magento\SalesSequence\Model\ResourceModel\Profile as ProfileResource;
use Magento\SalesSequence\Model\EntityPool;
use PHPUnit\Framework\TestCase;

/**
 * Test default created store views sales sequences prefix.
 */
class SequenceCreatorTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManger;

    /**
     * @var SequenceCreator
     */
    private $sequenceCreator;

    /**
     * @var EntityPool
     */
    private $entityPool;

    /**
     * @var MetaResource
     */
    private $sequenceMetaResource;

    /**
     * @var ProfileResource
     */
    private $sequenceProfileResource;

    /**
     * @var SequenceConfig
     */
    private $sequenceConfig;

    /**
     * @var AppResource
     */
    private $appResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManger = Bootstrap::getObjectManager();
        $this->sequenceCreator = $this->objectManger->create(SequenceCreator::class);
        $this->sequenceMetaResource = $this->objectManger->create(MetaResource::class);
        $this->sequenceProfileResource = $this->objectManger->create(ProfileResource::class);
        $this->entityPool = $this->objectManger->create(EntityPool::class);
        $this->sequenceConfig = $this->objectManger->create(SequenceConfig::class);
        $this->appResource = $this->objectManger->create(AppResource::class);
    }

    /**
     * Test prefix for default created store views.
     *
     * @param array $defaultStores
     * @dataProvider defaultStoresDataProvider
     */
    public function testSalesSequenceProfileTableForDefaultStores(array $defaultStores): void
    {
        foreach ($defaultStores as $storeId) {
            foreach ($this->entityPool->getEntities() as $entityType) {
                $meta = $this->sequenceMetaResource->loadByEntityTypeAndStore($entityType, $storeId);
                $profile = $this->sequenceProfileResource->loadActiveProfile($meta->getId());
                $this->assertEquals($this->getSequenceTableName($entityType, $storeId), $meta->getSequenceTable());
                $this->assertEquals($this->sequenceConfig->get('startValue'), $profile->getStartValue());
                $this->assertEquals($this->sequenceConfig->get('suffix'), $profile->getSuffix());
                $this->assertEquals($this->sequenceConfig->get('step'), $profile->getStep());
                $this->assertEquals($this->sequenceConfig->get('warningValue'), $profile->getWarningValue());
                $this->assertEquals($this->sequenceConfig->get('maxValue'), $profile->getMaxValue());

                if ($storeId === 0) {
                    $this->assertEquals(null, $profile->getPrefix());
                } else {
                    $this->assertNotNull($profile->getPrefix());
                }
            }
        }
    }

    /**
     * Default store codes data provider
     *
     * @return array
     */
    public function defaultStoresDataProvider(): array
    {
        return [
            [
                'defaultStores' => [
                    0,
                    1
                ]
            ],
        ];

    }

    /**
     * Get sequence table name
     *
     * @param string $entityType
     * @param int $storeId
     *
     * @return string
     */
    private function getSequenceTableName(string $entityType, int $storeId): string
    {
        return $this->appResource->getTableName(
            sprintf(
                'sequence_%s_%s',
                $entityType,
                $storeId
            )
        );
    }
}
