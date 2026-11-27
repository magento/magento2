<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model;

use Magento\Backend\Model\Locale\Resolver;
use Magento\Backend\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Cache\Type\Translate;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\TranslateInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\ImportExport\Test\Fixture\CsvFile;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Translation\Test\Fixture\Translation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AppArea('adminhtml')]
#[AppIsolation(true)]
#[DbIsolation(true)]
#[Config('general/locale/code', 'en_US')]
#[DataFixture(Translation::class, [
    'string' => 'Catalog, Search',
    'translate' => 'Catalogus, zoeken',
    'locale' => 'nl_NL',
])]
#[DataFixture(ProductFixture::class, ['visibility' => 1], 'product')]
#[DataFixture(CsvFile::class, ['rows' => [
    ['sku', 'visibility'],
    ['$product.sku$', 'Catalog, Search'],
]], 'english')]
#[DataFixture(CsvFile::class, ['rows' => [
    ['sku', 'visibility'],
    ['$product.sku$', 'Catalogus, zoeken'],
]], 'dutch')]
#[DataFixture(CsvFile::class, ['rows' => [
    ['sku', 'visibility'],
    ['$product.sku$', 'invalid-visibility'],
]], 'invalid')]
class AdminLocaleImportTest extends TestCase
{
    /**
     * @param string $file
     * @param bool $valid
     * @return void
     */
    #[DataProvider('importProvider')]
    public function testProductImportWithTranslatedAdmin(string $file, bool $valid): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $session = $objectManager->get(Session::class);
        $resolver = $objectManager->get(Resolver::class);
        $translator = $objectManager->get(TranslateInterface::class);
        $cache = $objectManager->get(Translate::class);
        $initialLocale = $resolver->getLocale();
        $initialSessionLocale = $session->getSessionLocale();
        $initialRenderer = Phrase::getRenderer();

        try {
            $session->setSessionLocale('nl_NL');
            $resolver->setLocale();
            $cache->clean();
            $translator->setLocale($resolver->getLocale())->loadData('adminhtml');
            Phrase::setRenderer($objectManager->get(RendererInterface::class));
            self::assertSame('nl_NL', $resolver->getLocale());
            self::assertSame('Catalogus, zoeken', (string)new Phrase('Catalog, Search'));
            $model = $objectManager->create(Import::class);
            $model->setData([
                'entity' => 'catalog_product',
                'behavior' => Import::BEHAVIOR_APPEND,
                Import::FIELD_NAME_VALIDATION_STRATEGY =>
                    ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR,
                Import::FIELD_NAME_ALLOWED_ERROR_COUNT => 0,
            ]);
            $directory = $objectManager->get(Filesystem::class)->getDirectoryRead(DirectoryList::ROOT);
            $source = $objectManager->create(Csv::class, [
                'file' => $fixtures->get($file)->getAbsolutePath(),
                'directory' => $directory,
            ]);
            self::assertSame($valid, $model->validateSource($source));
            if ($valid) {
                self::assertSame(0, $model->getErrorAggregator()->getErrorsCount());
                self::assertTrue($model->importSource());
            } else {
                $errors = $model->getErrorAggregator()->getAllErrors();
                self::assertNotEmpty($errors);
                self::assertStringContainsString("'visibility'", $errors[0]->getErrorMessage());
            }
            $product = $objectManager->get(ProductRepositoryInterface::class)
                ->get($fixtures->get('product')->getSku(), false, null, true);
            self::assertSame($valid ? 4 : 1, (int)$product->getVisibility());
            self::assertSame('nl_NL', $resolver->getLocale());
            self::assertSame('nl_NL', $session->getSessionLocale());
            self::assertSame('Catalogus, zoeken', (string)new Phrase('Catalog, Search'));
        } finally {
            $session->setSessionLocale($initialSessionLocale);
            $resolver->setLocale($initialLocale);
            $cache->clean();
            $translator->setLocale($initialLocale)->loadData('adminhtml');
            Phrase::setRenderer($initialRenderer);
        }
    }

    /**
     * @return array
     */
    public static function importProvider(): array
    {
        return [
            'English option' => ['english', true],
            'Dutch option' => ['dutch', true],
            'invalid option' => ['invalid', false],
        ];
    }
}
