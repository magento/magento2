<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model\Deploy;

use Magento\Deploy\Model\DeployManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Magento\Deploy\Console\Command\DeployStaticOptionsInterface as Options;
use \Magento\Framework\RequireJs\Config as RequireJsConfig;

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
     * @param Filesystem $filesystem
     * @param OutputInterface $output
     * @param array $options
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem, OutputInterface $output, $options = [])
    {
        $this->filesystem = $filesystem;
        $this->output = $output;
        $this->options = $options;
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
        if (isset($this->options[Options::DRY_RUN]) && $this->options[Options::DRY_RUN]) {
            return Cli::RETURN_SUCCESS;
        }

        $this->output->writeln("=== {$area} -> {$themePath} -> {$locale} ===");

        if (!isset($this->options[self::DEPLOY_BASE_LOCALE])) {
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

        if (isset($this->options[Options::SYMLINK_LOCALE]) && $this->options[Options::SYMLINK_LOCALE]) {
            $this->getStaticDirectory()->createSymlink($baseLocalePath, $newLocalePath);
            $this->getStaticDirectory()->createSymlink($baseRequireJsPath, $newRequireJsPath);

            $this->output->writeln("\nSuccessful symlinked\n---\n");
        } else {
            $localeFiles = array_merge(
                $this->getStaticDirectory()->readRecursively($baseLocalePath),
                $this->getStaticDirectory()->readRecursively($baseRequireJsPath)
            );
            foreach ($localeFiles as $path) {
                if ($this->getStaticDirectory()->isFile($path)) {
                    $destination = $this->replaceLocaleInPath($path, $baseLocale, $locale);
                    $this->getStaticDirectory()->copyFile($path, $destination);
                    $processedFiles++;
                }
            }

            $this->output->writeln("\nSuccessful copied: {$processedFiles} files; errors: {$errorAmount}\n---\n");
        }

        return Cli::RETURN_SUCCESS;
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
