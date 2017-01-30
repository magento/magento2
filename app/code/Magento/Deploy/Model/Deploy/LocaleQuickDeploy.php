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
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\ObjectManager;
use Magento\Translation\Model\Js\Config as TranslationJsConfig;
use Magento\Framework\TranslateInterface;

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
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var Publisher
     */
    private $assetPublisher;

    /**
     * @var TranslationJsConfig
     */
    private $translationJsConfig;

    /**
     * @var TranslateInterface
     */
    private $translator;

    /**
     * @param Filesystem $filesystem
     * @param OutputInterface $output
     * @param array $options
     * @param Repository $assetRepo
     * @param Publisher $assetPublisher
     * @param TranslationJsConfig $translationJsConfig
     * @param TranslateInterface $translator
     */
    public function __construct(
        Filesystem $filesystem,
        OutputInterface $output,
        $options = [],
        Repository $assetRepo = null,
        Publisher $assetPublisher = null,
        TranslationJsConfig $translationJsConfig = null,
        TranslateInterface $translator = null
    ) {
        $this->filesystem = $filesystem;
        $this->output = $output;
        $this->options = $options;
        $this->assetRepo = $assetRepo ?: ObjectManager::getInstance()->get(Repository::class);
        $this->assetPublisher = $assetPublisher ?: ObjectManager::getInstance()->get(Publisher::class);
        $this->translationJsConfig = $translationJsConfig
            ?: ObjectManager::getInstance()->get(TranslationJsConfig::class);
        $this->translator = $translator ?: ObjectManager::getInstance()->get(TranslateInterface::class);
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
                    $destination = $this->replaceLocaleInPath($path, $baseLocale, $locale);
                    if (!$jsDictionaryEnabled || !$this->isJsDictionary($path)) {
                        $this->getStaticDirectory()->copyFile($path, $destination);
                    }
                    $processedFiles++;
                }
            }

            if ($jsDictionaryEnabled) {
                $this->deployJsDictionary($area, $themePath, $locale);
                $processedFiles++;
            }

            $this->output->writeln("\nSuccessful copied: {$processedFiles} files; errors: {$errorAmount}\n---\n");
        }

        return Cli::RETURN_SUCCESS;
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
     * Deploy js-dictionary for specific locale, theme and area
     *
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @return void
     */
    private function deployJsDictionary($area, $themePath, $locale)
    {
        $this->translator->setLocale($locale);
        $this->translator->loadData($area, true);

        $asset = $this->assetRepo->createAsset(
            $this->translationJsConfig->getDictionaryFileName(),
            ['area' => $area, 'theme' => $themePath, 'locale' => $locale]
        );
        if ($this->output->isVeryVerbose()) {
            $this->output->writeln("\tDeploying the file to '{$asset->getPath()}'");
        } else {
            $this->output->write('.');
        }

        $this->assetPublisher->publish($asset);
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
