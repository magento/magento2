<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\DataFixtureTestStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 */
class DataFixtureTest extends TestCase
{
    /**
     * @var DataFixtureTestStorage
     */
    private $dataStorage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dataStorage = Bootstrap::getObjectManager()->get(DataFixtureTestStorage::class);
    }

    /**
     * @magentoDataFixture afterTestFixtureClass
     * @magentoDataFixture Magento\TestFramework\Fixture\TestOne
     * @magentoDataFixture Magento\TestFramework\Fixture\TestTwo
     */
    public function testFixtureClass(): void
    {
        $this->assertEquals(
            [
                'Magento\TestFramework\Fixture\TestOne' => true,
                'Magento\TestFramework\Fixture\TestTwo' => true
            ],
            $this->dataStorage->getData('fixtures')
        );
    }

    /**
     * @magentoDataFixture afterTestFixtureClass
     * @magentoDataFixture Magento\TestFramework\Fixture\TestOne with:{"test1": "value1", "test11": "value11"}
     * @magentoDataFixture Magento\TestFramework\Fixture\TestTwo with:{"test2": "value2"}
     * @magentoDataFixture Magento\TestFramework\Fixture\TestThree with:{"key": "test11", "value": "value12"}
     */
    public function testFixtureClassWithParameters(): void
    {
        $this->assertEquals(
            [
                'Magento\TestFramework\Fixture\TestOne' => true,
                'Magento\TestFramework\Fixture\TestTwo' => true,
                'test1' => 'value1',
                'test11' => 'value12',
                'test2' => 'value2',
            ],
            $this->dataStorage->getData('fixtures')
        );
    }

    /**
     * @magentoDataFixture afterTestFixtureClass
     * @magentoDataFixture Magento\TestFramework\Fixture\TestOne with:{"test1": "value1", "test11": "value11"} as:test1
     * @magentoDataFixture Magento\TestFramework\Fixture\TestTwo with:{"test2": "value2"} as:test2
     * @magentoDataFixture Magento\TestFramework\Fixture\TestThree with:{"key": "test11", "value": "value12"} as:test3
     */
    public function testFixtureClassWithParametersAndAlias(): void
    {
        $this->assertEquals(
            [
                'Magento\TestFramework\Fixture\TestOne' => true,
                'test1' => 'value1',
                'test11' => 'value11',
            ],
            DataFixtureStorageManager::getStorage()->get('test1')->getData()
        );
        $this->assertEquals(
            [
                'Magento\TestFramework\Fixture\TestTwo' => true,
                'test2' => 'value2',
            ],
            DataFixtureStorageManager::getStorage()->get('test2')->getData()
        );
        $this->assertNull(
            DataFixtureStorageManager::getStorage()->get('test3')
        );
        $this->assertEquals(
            [
                'Magento\TestFramework\Fixture\TestOne' => true,
                'Magento\TestFramework\Fixture\TestTwo' => true,
                'test1' => 'value1',
                'test11' => 'value12',
                'test2' => 'value2',
            ],
            $this->dataStorage->getData('fixtures')
        );
    }

    public static function afterTestFixtureClass(): void
    {
        self::assertEmpty(Bootstrap::getObjectManager()->get(DataFixtureTestStorage::class)->getData('fixtures'));
    }

    public static function afterTestFixtureClassRollback(): void
    {
        self::assertEmpty(Bootstrap::getObjectManager()->get(DataFixtureTestStorage::class)->getData('fixtures'));
    }
}
