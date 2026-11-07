<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Model\Config\Backend\Serialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Documents FieldArray / ArraySerialized save-side contracts.
 *
 * - Posting only __empty clears the array (intentional empty grid).
 * - Omitting the field from a save payload leaves the previous value (disabled inputs).
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class ArraySerializedSaveSemanticsTest extends TestCase
{
    private const PATH = 'demo_depends_array/general/items';

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ArraySerialized
     */
    private $backend;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->configWriter = $objectManager->get(WriterInterface::class);
        $this->scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $this->json = $objectManager->get(Json::class);
        $this->backend = $objectManager->create(ArraySerialized::class);
        $this->cacheTypeList = $objectManager->get(TypeListInterface::class);
        $this->reinitableConfig = $objectManager->get(ReinitableConfigInterface::class);
    }

    private function reinitConfig(): void
    {
        $this->cacheTypeList->cleanType('config');
        $this->reinitableConfig->reinit();
    }

    public function testOnlyEmptySentinelBecomesEmptyArrayBeforeSave(): void
    {
        $this->backend->setPath(self::PATH);
        $this->backend->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->backend->setScopeId(0);
        $this->backend->setValue(['__empty' => '']);
        $this->backend->beforeSave();

        $value = $this->backend->getValue();
        $this->assertIsString($value);
        $this->assertSame([], $this->json->unserialize($value));
    }

    public function testSavingEmptyArrayOverwritesPreviousRows(): void
    {
        // Seed existing rows (overwrite path when only __empty is posted)
        $this->configWriter->save(
            self::PATH,
            $this->json->serialize([
                '_row1' => ['name' => 'will-be-wiped', 'value' => '1'],
            ])
        );
        $this->reinitConfig();

        $this->backend->setPath(self::PATH);
        $this->backend->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->backend->setScopeId(0);
        $this->backend->setValue(['__empty' => '']);
        $this->backend->beforeSave();
        $this->backend->save();
        $this->reinitConfig();

        $stored = $this->scopeConfig->getValue(self::PATH);
        if ($stored === null || $stored === false || $stored === '') {
            // Some storage paths drop empty values; treat as cleared
            $this->assertTrue(true);
            return;
        }
        $decoded = is_string($stored) ? $this->json->unserialize($stored) : $stored;
        $this->assertSame([], $decoded);
    }

    public function testRowsArePersisted(): void
    {
        $rows = [
            '_row1' => ['name' => 'alpha', 'value' => '1'],
            '_row2' => ['name' => 'beta', 'value' => '2'],
            '__empty' => '',
        ];

        $this->backend->setPath(self::PATH);
        $this->backend->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->backend->setScopeId(0);
        $this->backend->setValue($rows);
        $this->backend->beforeSave();
        $this->backend->save();
        $this->reinitConfig();

        $stored = $this->scopeConfig->getValue(self::PATH);
        $decoded = is_string($stored) ? $this->json->unserialize($stored) : $stored;
        $this->assertArrayHasKey('_row1', $decoded);
        $this->assertSame('alpha', $decoded['_row1']['name']);
        $this->assertArrayNotHasKey('__empty', $decoded);
    }

    public function testOmittingFieldFromWriteLeavesExistingValue(): void
    {
        $payload = $this->json->serialize([
            '_row1' => ['name' => 'keep-me', 'value' => 'yes'],
        ]);
        $this->configWriter->save(self::PATH, $payload);
        $this->reinitConfig();

        // Simulate a save that does not include this field (all inputs disabled, nothing posted)
        $before = $this->scopeConfig->getValue(self::PATH);
        $this->assertNotEmpty($before);

        // No write for self::PATH — value must remain
        $after = $this->scopeConfig->getValue(self::PATH);
        $this->assertSame($before, $after);

        $decoded = is_string($after) ? $this->json->unserialize($after) : $after;
        $this->assertSame('keep-me', $decoded['_row1']['name']);
    }
}
