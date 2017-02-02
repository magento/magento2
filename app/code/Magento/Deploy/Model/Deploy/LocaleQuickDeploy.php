<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model\Deploy;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Magento\Deploy\Console\Command\DeployStaticOptionsInterface as Options;
use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\Translate\Js\Config as TranslationJsConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Deploy\Model\DeployStrategyFactory;

class LocaleQuickDeploy implements DeployInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteInterface
     */
    private $staticDirectory;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var TranslationJsConfig
     */
    private $translationJsConfig;

    /**
     * @var DeployStrategyFactory
     */
    private $deployStrategyFactory;

    /**
     * @var DeployInterface[]
     */
    private $deploys;

    /**
     * @param Filesystem $filesystem
     * @param OutputInterface $output
     * @param array $options
     * @param TranslationJsConfig $translationJsConfig
     * @param DeployStrategyFactory $deployStrategyFactory
     */
    public function __construct(
        Filesystem $filesystem,
        OutputInterface $output,
        $options = [],
        TranslationJsConfig $translationJsConfig = null,
        DeployStrategyFactory $deployStrategyFactory = null
    ) {
        $this->filesystem = $filesystem;
        $this->output = $output;
        $this->options = $options;
        $this->translationJsConfig = $translationJsConfig
            ?: ObjectManager::getInstance()->get(TranslationJsConfig::class);
        $this->deployStrategyFactory = $deployStrategyFactory
            ?: ObjectManager::getInstance()->get(DeployStrategyFactory::class);
    }

    /**
     * @return WriteInterface
     */
    private function getStaticDirectory()
    {
        if ($this->staticDirectory === null) {
            $this->staticDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        }

        return $this->staticDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function deploy($area, $themePath, $locale)
    {
        if (!empty($this->options[Options::DRY_RUN])) {
            return Cli::RETURN_SUCCESS;
        }

        $this->output->writeln("=== {$area} -> {$themePath} -> {$locale} ===");

        if (empty($this->options[self::DEPLOY_BASE_LOCALE])) {
            throw new \InvalidArgumentException('Deploy base locale must be set for Quick Deploy');
        }
        $processedFiles = 0;
        $errorAmount = 0;

        $baseLocale = $this->options[self::DEPLOY_BASE_LOCALE];
        $newLocalePath = $this->getLocalePath($area, $themePath, $locale);
        $baseLocalePath = $this->getLocalePath($area, $themePath, $baseLocale);
        $baseRequireJsPath = RequireJsConfig::DIR_NAME . DIRECTORY_SEPARATOR . $baseLocalePath;
        $newRequireJsPath = RequireJsConfig::DIR_NAME . DIRECTORY_SEPARATOR . $newLocalePath;

        $this->deleteLocaleResource($newLocalePath);
        $this->deleteLocaleResource($newRequireJsPath);

        if (!empty($this->options[Options::SYMLINK_LOCALE])) {
            $this->getStaticDirectory()->createSymlink($baseLocalePath, $newLocalePath);
            $this->getStaticDirectory()->createSymlink($baseRequireJsPath, $newRequireJsPath);

            $this->output->writeln("\nSuccessful symlinked\n---\n");
        } else {
            $localeFiles = array_merge(
                $this->getStaticDirectory()->readRecursively($baseLocalePath),
                $this->getStaticDirectory()->readRecursively($baseRequireJsPath)
            );
            $jsDictionaryEnabled = $this->translationJsConfig->dictionaryEnabled();
            foreach ($localeFiles as $path) {
                if ($this->getStaticDirectory()->isFile($path)) {
                    if (!$jsDictionaryEnabled || !$this->isJsDictionary($path)) {
                        $destination = $this->replaceLocaleInPath($path, $baseLocale, $locale);
                        $this->getStaticDirectory()->copyFile($path, $destination);
                        $processedFiles++;
                    }
                }
            }

            if ($jsDictionaryEnabled) {
                $this->getDeploy(
                    DeployStrategyFactory::DEPLOY_STRATEGY_JS_DICTIONARY,
                    [
                        'output' => $this->output,
                        'translationJsConfig' => $this->translationJsConfig
                    ]
                )
                ->deploy($area, $themePath, $locale);
                $processedFiles++;
            }

            $this->output->writeln("\nSuccessful copied: {$processedFiles} files; errors: {$errorAmount}\n---\n");
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Get deploy strategy according to required strategy
     *
     * @param string $strategy
     * @param array $params
     * @return DeployInterface
     */
    private function getDeploy($strategy, $params)
    {
        if (empty($this->deploys[$strategy])) {
            $this->deploys[$strategy] = $this->deployStrategyFactory->create($strategy, $params);
        }
        return $this->deploys[$strategy];
    }

    /**
     * Define if provided path is js dictionary
     *
     * @param string $path
     * @return bool
     */
    private function isJsDictionary($path)
    {
        return strpos($path, $this->translationJsConfig->getDictionaryFileName()) !== false;
    }

    /**
     * @param string $path
     * @return void
     */
    private function deleteLocaleResource($path)
    {
        if ($this->getStaticDirectory()->isExist($path)) {
            $absolutePath = $this->getStaticDirectory()->getAbsolutePath($path);
            if (is_link($absolutePath)) {
                $this->getStaticDirectory()->getDriver()->deleteFile($absolutePath);
            } else {
                $this->getStaticDirectory()->getDriver()->deleteDirectory($absolutePath);
            }
        }
    }

    /**
     * @param string $path
     * @param string $search
     * @param string $replace
     * @return string
     */
    private function replaceLocaleInPath($path, $search, $replace)
    {
        return preg_replace('~' . $search . '~', $replace, $path, 1);
    }

    /**
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @return string
     */
    private function getLocalePath($area, $themePath, $locale)
    {
        return $area . DIRECTORY_SEPARATOR . $themePath . DIRECTORY_SEPARATOR . $locale;
    }
}
