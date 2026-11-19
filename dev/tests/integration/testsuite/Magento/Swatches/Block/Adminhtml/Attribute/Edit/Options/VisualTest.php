<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options;

use DOMDocument;
use DOMXPath;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verify that visual swatch footers span every header column.
 */
#[AppArea('adminhtml')]
class VisualTest extends TestCase
{
    private const ATTRIBUTE_DATA = [
        'frontend_input' => 'select',
        'backend_type' => 'int',
        'swatch_input_type' => 'visual',
    ];

    #[DataFixture(AttributeFixture::class, self::ATTRIBUTE_DATA, as: 'attribute')]
    public function testFooterColspansWithDefaultStore(): void
    {
        $this->assertFooterColspans(false);
    }

    #[DbIsolation(false)]
    #[DataFixture(AttributeFixture::class, self::ATTRIBUTE_DATA, as: 'attribute')]
    #[DataFixture(StoreFixture::class, as: 'extra_store')]
    public function testFooterColspansWithExtraStore(): void
    {
        $this->assertFooterColspans(true);
    }

    /**
     * Render with an explicit store list so installed store views do not affect either case.
     *
     * @param bool $includeExtraStore
     * @return void
     */
    private function assertFooterColspans(bool $includeExtraStore): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $stores = [$objectManager->get(StoreManagerInterface::class)->getStore(0)];
        if ($includeExtraStore) {
            $stores[] = $fixtures->get('extra_store');
        }

        $registry = $objectManager->get(Registry::class);
        $registry->register('entity_attribute', $fixtures->get('attribute'));
        try {
            $block = $objectManager->create(Visual::class);
            $block->setStores($stores);
            $document = new DOMDocument();
            $this->assertTrue($document->loadHTML($block->toHtml(), LIBXML_NOERROR | LIBXML_NOWARNING));
            $xpath = new DOMXPath($document);
            $table = '//div[@id="swatch-visual-options-panel"]/table';
            $headers = $xpath->query($table . '/thead/tr/th');
            $this->assertCount(count($stores) + 4, $headers);
            $footers = $xpath->query($table . '/tfoot//@colspan');
            $this->assertCount(2, $footers);
            foreach ($footers as $footer) {
                $this->assertSame($headers->length, (int)$footer->nodeValue);
            }
        } finally {
            $registry->unregister('entity_attribute');
        }
    }
}
