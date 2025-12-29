<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Plugin\Cron\Model\Config\Backend;

use Magento\Cron\Model\Config\Backend\Sitemap;
use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sitemap\Model\Config\Source\GenerationMethod;

/**
 * Plugin for Cron Sitemap backend model to ensure extended cron job configuration
 */
class SitemapPlugin
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
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * @param ValueFactory $configValueFactory
     */
    public function __construct(
        ValueFactory $configValueFactory
    ) {
        $this->configValueFactory = $configValueFactory;
    }

    /**
     * Plugin to override the afterSave behavior to use unified cron job
     *
     * @param Sitemap $subject
     * @param mixed $result
     * @return mixed
     * @throws LocalizedException
     */
    public function afterAfterSave(
        Sitemap $subject,
        mixed $result
    ) {
        $config = $subject->getConfig();

        $time = $this->getTimeConfiguration($subject, $config);
        $frequency = $subject->getValue();
        $generationMethod = $this->getGenerationMethod($subject, $config);

        $cronExprString = $this->buildCronExpression($time, $frequency);
        $observerModel = $this->getObserverModel($generationMethod);

        $this->updateCronConfiguration($cronExprString, $observerModel);

        return $result;
    }

    /**
     * Get time configuration from subject or config
     *
     * @param Sitemap $subject
     * @param ScopeConfigInterface|null $config
     * @return array
     * @throws LocalizedException
     */
    private function getTimeConfiguration(Sitemap $subject, $config): array
    {
        $time = $subject->getData('groups/generate/fields/time/value');
        if (!$time && $config) {
            $timeConfig = $config->getValue(
                'sitemap/generate/time',
                $subject->getScope(),
                $subject->getScopeId()
            );
            $time = $timeConfig ? explode(',', $timeConfig) : null;
        }

        if (!$time) {
            $recentTimeConfig = $this->getRecentlySavedTimeConfiguration();
            $time = $recentTimeConfig ? explode(',', $recentTimeConfig) : null;
        }

        if (!is_array($time) || empty($time)) {
            $time = ['0', '0', '0'];
        }

        while (count($time) < 3) {
            $time[] = '0';
        }

        return $time;
    }

    /**
     * Get recently saved time configuration from config value factory
     *
     * @return string|null
     * @throws LocalizedException
     */
    private function getRecentlySavedTimeConfiguration(): ?string
    {
        $configValue = $this->configValueFactory->create()->load(
            'sitemap/generate/time',
            'path'
        );

        return $configValue->getId() ? $configValue->getValue() : null;
    }

    /**
     * Get generation method from various sources
     *
     * @param Sitemap $subject
     * @param ScopeConfigInterface|null $config
     * @return string
     * @throws LocalizedException
     */
    private function getGenerationMethod(Sitemap $subject, ?ScopeConfigInterface $config): string
    {
        $generationMethod = $subject->getData('groups/generate/fields/generation_method/value');

        if (!$generationMethod && $config) {
            $generationMethod = $config->getValue(
                'sitemap/generate/generation_method',
                $subject->getScope(),
                $subject->getScopeId()
            );
        }

        if (!$generationMethod) {
            $generationMethod = $this->getRecentlySavedGenerationMethod();
        }

        return $generationMethod ?: GenerationMethod::STANDARD;
    }

    /**
     * Get recently saved generation method from config value factory
     *
     * @return string|null
     * @throws LocalizedException
     */
    private function getRecentlySavedGenerationMethod(): ?string
    {
        $configValue = $this->configValueFactory->create()->load(
            'sitemap/generate/generation_method',
            'path'
        );

        return $configValue->getId() ? $configValue->getValue() : null;
    }

    /**
     * Build cron expression from time and frequency
     *
     * @param array $time
     * @param string $frequency
     * @return string
     */
    private function buildCronExpression(array $time, string $frequency): string
    {
        $cronExprArray = [
            (int)($time[1] ?? 0), //Minute
            (int)($time[0] ?? 0), //Hour
            $frequency == Frequency::CRON_MONTHLY ? '1' : '*', //Day of the Month
            '*', //Month of the Year
            $frequency == Frequency::CRON_WEEKLY ? '1' : '*', //# Day of the Week
        ];

        return join(' ', $cronExprArray);
    }

    /**
     * Get observer model based on generation method
     *
     * @param string $generationMethod
     * @return string
     */
    private function getObserverModel(string $generationMethod): string
    {
        return $generationMethod === GenerationMethod::BATCH
            ? 'Magento\Sitemap\Model\Batch\Observer::scheduledGenerateSitemaps'
            : 'Magento\Sitemap\Model\Observer::scheduledGenerateSitemaps';
    }

    /**
     * Update cron configuration with new values
     *
     * @param string $cronExprString
     * @param string $observerModel
     * @return void
     * @throws LocalizedException
     */
    private function updateCronConfiguration(string $cronExprString, string $observerModel): void
    {
        try {
            $this->clearCronConfiguration(self::CRON_STRING_PATH);
            $this->clearCronConfiguration(self::CRON_MODEL_PATH);

            $this->setCronConfiguration(self::CRON_STRING_PATH, $cronExprString);
            $this->setCronConfiguration(self::CRON_MODEL_PATH, $observerModel);
        } catch (\Exception $e) {
            throw new LocalizedException(__('We can\'t save the cron expression.'));
        }
    }

    /**
     * Set cron configuration value
     *
     * @param string $path
     * @param string $value
     * @return void
     * @throws LocalizedException
     */
    private function setCronConfiguration(string $path, string $value): void
    {
        $this->configValueFactory->create()->load(
            $path,
            'path'
        )->setValue(
            $value
        )->setPath(
            $path
        )->save();
    }

    /**
     * Clear cron configuration value
     *
     * @param string $path
     * @return void
     * @throws LocalizedException
     */
    private function clearCronConfiguration(string $path): void
    {
        $configValue = $this->configValueFactory->create()->load($path, 'path');
        if ($configValue->getId()) {
            $configValue->delete();
        }
    }
}
