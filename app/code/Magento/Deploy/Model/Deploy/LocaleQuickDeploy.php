<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @param Filesystem $filesystem
     * @param OutputInterface $output
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem, OutputInterface $output)
    {
        $this->filesystem = $filesystem;
        $this->output = $output;
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
    public function deploy($area, $themePath, $locale, array $options)
    {
        $this->output->writeln("=== {$area} -> {$themePath} -> {$locale} ===");

        if (!isset($options[DeployManager::DEPLOY_BASE_LOCALE])) {
            throw new \InvalidArgumentException('Deploy base locale must be set for Quick Deploy');
        }
        $processedFiles = 0;
        $errorAmount = 0;

        $baseLocale = $options[DeployManager::DEPLOY_BASE_LOCALE];
        $newLocalePath =$this->getLocalePath($area, $themePath, $locale);
        $baseLocalePath = $this->getLocalePath($area, $themePath, $baseLocale);
        $baseRequireJsPath = RequireJsConfig::DIR_NAME . DIRECTORY_SEPARATOR . $baseLocalePath;
        $newRequireJsPath = RequireJsConfig::DIR_NAME . DIRECTORY_SEPARATOR . $newLocalePath;

        $this->getStaticDirectory()->delete($newLocalePath);
        $this->getStaticDirectory()->delete($newRequireJsPath);

        if ($options[Options::SYMLINK_LOCALE]) {
            $this->getStaticDirectory()->createSymlink($baseLocalePath, $newLocalePath);
            $this->getStaticDirectory()->createSymlink($baseRequireJsPath, $newRequireJsPath);

            $this->output->writeln("\nSuccessful symlinked\n---\n");
        } else {
            $localeFiles = Files::getFiles(
                [
                    $this->getStaticDirectory()->getAbsolutePath($baseLocalePath),
                    $this->getStaticDirectory()->getAbsolutePath($baseRequireJsPath)
                ],
                '*'
            );
            foreach ($localeFiles as $file) {
                $path = $this->getStaticDirectory()->getRelativePath($file);
                $destination = $this->replaceLocaleInPath($path, $baseLocale, $locale);
                $this->getStaticDirectory()->copyFile($path, $destination);
                $processedFiles++;
            }

            $this->output->writeln("\nSuccessful copied: {$processedFiles} files; errors: {$errorAmount}\n---\n");
        }

        return Cli::RETURN_SUCCESS;
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
