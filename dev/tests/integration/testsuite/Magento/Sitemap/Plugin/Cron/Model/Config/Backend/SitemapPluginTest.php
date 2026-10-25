<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Plugin\Cron\Model\Config\Backend;

use Magento\Config\Model\Config as ConfigModel;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sitemap\Model\Config\Source\GenerationMethod;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for SitemapPlugin
 *
 * @magentoAppArea adminhtml
 */
class SitemapPluginTest extends TestCase
{
    /**
     * Cron string path for sitemap schedule
     */
    private const CRON_STRING_PATH = 'crontab/default/jobs/sitemap_generate/schedule/cron_expr';

    /**
     * Cron model path
     */
    private const CRON_MODEL_PATH = 'crontab/default/jobs/sitemap_generate/run/model';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->configValueFactory = $this->objectManager->get(ValueFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->clearCronConfiguration(self::CRON_STRING_PATH);
        $this->clearCronConfiguration(self::CRON_MODEL_PATH);
        $this->clearConfig('sitemap/generate/generation_method');
        parent::tearDown();
    }

    /**
     * Test that sitemap cron configuration uses standard observer when generation method is standard
     */
    #[DataProvider('frequencyDataProvider')]
    public function testSitemapCronConfigurationWithStandardMethod(string $frequency, string $expectedCronExpr): void
    {
        $this->setConfig('sitemap/generate/generation_method', GenerationMethod::STANDARD);

        $config = $this->objectManager->create(ConfigModel::class);
        $config->setSection('sitemap');
        $config->setGroups([
            'generate' => [
                'fields' => [
                    'time' => ['value' => ['00', '00', '00']],
                    'frequency' => ['value' => $frequency],
                    'generation_method' => ['value' => GenerationMethod::STANDARD],
                ],
            ],
        ]);
        $config->save();

        $this->assertEquals($expectedCronExpr, $this->getCronExpression());

        $this->assertEquals(
            'Magento\Sitemap\Model\Observer::scheduledGenerateSitemaps',
            $this->getCronModel()
        );
    }

    /**
     * Test that sitemap cron configuration uses batch observer when generation method is batch
     */
    #[DataProvider('frequencyDataProvider')]
    public function testSitemapCronConfigurationWithBatchMethod(string $frequency, string $expectedCronExpr): void
    {
        $this->setConfig('sitemap/generate/generation_method', GenerationMethod::BATCH);

        $config = $this->objectManager->create(ConfigModel::class);
        $config->setSection('sitemap');
        $config->setGroups([
            'generate' => [
                'fields' => [
                    'time' => ['value' => ['00', '00', '00']],
                    'frequency' => ['value' => $frequency],
                    'generation_method' => ['value' => GenerationMethod::BATCH],
                ],
            ],
        ]);
        $config->save();

        $this->assertEquals($expectedCronExpr, $this->getCronExpression());

        $this->assertEquals(
            'Magento\Sitemap\Model\Batch\Observer::scheduledGenerateSitemaps',
            $this->getCronModel()
        );
    }

    /**
     * Test that only one unified cron job is created regardless of generation method
     */
    public function testUnifiedCronJobCreation(): void
    {
        $config = $this->objectManager->create(ConfigModel::class);
        $config->setSection('sitemap');
        $config->setGroups([
            'generate' => [
                'fields' => [
                    'time' => ['value' => ['00', '00', '00']],
                    'frequency' => ['value' => Frequency::CRON_DAILY],
                    'generation_method' => ['value' => GenerationMethod::STANDARD],
                ],
            ],
        ]);
        $config->save();

        $this->assertNotEmpty($this->getCronExpression());
        $this->assertNotEmpty($this->getCronModel());

        $batchCronExpr = $this->configValueFactory->create()
            ->load('crontab/default/jobs/sitemap_generate_batch/schedule/cron_expr', 'path')
            ->getValue();
        $this->assertEmpty($batchCronExpr);

        $config->setGroups([
            'generate' => [
                'fields' => [
                    'time' => ['value' => ['00', '00', '00']],
                    'frequency' => ['value' => Frequency::CRON_DAILY],
                    'generation_method' => ['value' => GenerationMethod::BATCH],
                ],
            ],
        ]);
        $config->save();

        $this->assertEquals('0 0 * * *', $this->getCronExpression());
        $this->assertEquals(
            'Magento\Sitemap\Model\Batch\Observer::scheduledGenerateSitemaps',
            $this->getCronModel()
        );

        $batchCronExpr = $this->configValueFactory->create()
            ->load('crontab/default/jobs/sitemap_generate_batch/schedule/cron_expr', 'path')
            ->getValue();
        $this->assertEmpty($batchCronExpr);
    }

    /**
     * Test that cron configuration with custom time works correctly
     */
    public function testCustomTimeConfiguration(): void
    {
        $config = $this->objectManager->create(ConfigModel::class);
        $config->setSection('sitemap');
        $config->setGroups([
            'generate' => [
                'fields' => [
                    'time' => ['value' => ['03', '30', '00']], // 3:30 AM
                    'frequency' => ['value' => Frequency::CRON_DAILY],
                    'generation_method' => ['value' => GenerationMethod::BATCH],
                ],
            ],
        ]);
        $config->save();

        // Verify custom time is applied
        $this->assertEquals('30 3 * * *', $this->getCronExpression());
        $this->assertEquals(
            'Magento\Sitemap\Model\Batch\Observer::scheduledGenerateSitemaps',
            $this->getCronModel()
        );
    }

    /**
     * Test that direct save without admin context works
     */
    public function testDirectSaveWithGenerationMethod(): void
    {
        $this->setConfig('sitemap/generate/generation_method', GenerationMethod::BATCH);

        $preparedValueFactory = $this->objectManager->get(PreparedValueFactory::class);
        $sitemapValue = $preparedValueFactory->create(
            'sitemap/generate/frequency',
            Frequency::CRON_WEEKLY,
            'default',
            0
        );
        $sitemapValue->save();

        $this->assertEquals('0 0 * * 1', $this->getCronExpression());
        $this->assertEquals(
            'Magento\Sitemap\Model\Batch\Observer::scheduledGenerateSitemaps',
            $this->getCronModel()
        );
    }

    /**
     * @return array
     */
    public static function frequencyDataProvider(): array
    {
        return [
            'daily' => [Frequency::CRON_DAILY, '0 0 * * *'],
            'weekly' => [Frequency::CRON_WEEKLY, '0 0 * * 1'],
            'monthly' => [Frequency::CRON_MONTHLY, '0 0 1 * *'],
        ];
    }

    /**
     * Get cron expression from configuration
     *
     * @return string
     */
    private function getCronExpression(): string
    {
        $cronExprValue = $this->configValueFactory->create()
            ->load(self::CRON_STRING_PATH, 'path');

        return $cronExprValue->getValue() ?: '';
    }

    /**
     * Get cron model from configuration
     *
     * @return string
     */
    private function getCronModel(): string
    {
        $cronModelValue = $this->configValueFactory->create()
            ->load(self::CRON_MODEL_PATH, 'path');

        return $cronModelValue->getValue() ?: '';
    }

    /**
     * Set configuration value
     *
     * @param string $path
     * @param mixed $value
     * @return void
     */
    private function setConfig(string $path, $value): void
    {
        $this->configValueFactory->create()
            ->load($path, 'path')
            ->setValue($value)
            ->setPath($path)
            ->save();
    }

    /**
     * Clear configuration value
     *
     * @param string $path
     * @return void
     */
    private function clearConfig(string $path): void
    {
        $configValue = $this->configValueFactory->create()->load($path, 'path');
        if ($configValue->getId()) {
            $configValue->delete();
        }
    }

    /**
     * Clear cron configuration value
     *
     * @param string $path
     * @return void
     */
    private function clearCronConfiguration(string $path): void
    {
        $configValue = $this->configValueFactory->create()->load($path, 'path');
        if ($configValue->getId()) {
            $configValue->delete();
        }
    }
}
