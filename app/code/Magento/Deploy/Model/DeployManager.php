<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Magento\Framework\App\View\Deployment\Version\StorageInterface;
use Magento\Framework\View\Template\Html\MinifierInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\View\Asset\ConfigInterface as AssetConfig;
use Magento\Deploy\Console\Command\DeployStaticOptionsInterface as Options;
use Magento\Framework\App\Utility\Files;

class DeployManager
{
    /**
     * Base locale without customizations
     */
    const DEPLOY_BASE_LOCALE = 'deploy_base_locale';

    /**
     * @var array
     */
    private $packages = [];

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $options;

    /**
     * @var AssetConfig
     */
    private $assetConfig;

    /**
     * @var Files
     */
    private $filesUtils;

    /**
     * @var StorageInterface
     */
    private $versionStorage;

    /**
     * @var MinifierInterface
     */
    private $htmlMinifier;

    /**
     * @var DeployStrategyProviderFactory
     */
    private $deployStrategyProviderFactory;

    /**
     * @param OutputInterface $output
     * @param AssetConfig $assetConfig
     * @param Files $filesUtils
     * @param StorageInterface $versionStorage
     * @param MinifierInterface $htmlMinifier
     * @param DeployStrategyProviderFactory $deployStrategyProviderFactory
     * @param array $options
     * @internal param RulePool $rulePool
     * @internal param DesignInterface $design
     */
    public function __construct(
        OutputInterface $output,
        AssetConfig $assetConfig,
        Files $filesUtils,
        StorageInterface $versionStorage,
        MinifierInterface $htmlMinifier,
        DeployStrategyProviderFactory $deployStrategyProviderFactory,
        array $options
    ) {
        $this->output = $output;
        $this->options = $options;
        $this->assetConfig = $assetConfig;
        $this->filesUtils = $filesUtils;
        $this->versionStorage = $versionStorage;
        $this->htmlMinifier = $htmlMinifier;
        $this->deployStrategyProviderFactory = $deployStrategyProviderFactory;
    }

    /**
     * Add package tie to area and theme
     *
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @return void
     */
    public function addPack($area, $themePath, $locale)
    {
        $this->packages[$area . '-' . $themePath][$locale] = [$area, $themePath];
    }

    /**
     * Deploy local packages with chosen deploy strategy
     * @return int
     */
    public function deploy()
    {
        if (isset($this->options[Options::DRY_RUN]) && $this->options[Options::DRY_RUN]) {
            $this->output->writeln('Dry run. Nothing will be recorded to the target directory.');
        }

        $result = 0;
        /** @var DeployStrategyProvider $strategyProvider */
        $strategyProvider = $this->deployStrategyProviderFactory->create(
            ['output' => $this->output, 'options' => $this->options]
        );
        foreach ($this->packages as $package) {
            $locales = array_keys($package);
            list($area, $themePath) = current($package);

            foreach ($strategyProvider->getDeployStrategies($area, $themePath, $locales) as $locale => $strategy) {
                $result |= $strategy->deploy($area, $themePath, $locale);
            }
        }

        return $result;
    }

    /**
     * Minify template files
     * @return void
     */
    public function minifyTemplates()
    {
        $noHtmlMinify = isset($this->options[Options::NO_HTML_MINIFY]) ? $this->options[Options::NO_HTML_MINIFY] : null;
        if (!($noHtmlMinify ?: !$this->assetConfig->isMinifyHtml())) {
            $this->output->writeln('=== Minify templates ===');
            $count = 0;
            foreach ($this->filesUtils->getPhtmlFiles(false, false) as $template) {
                $this->htmlMinifier->minify($template);
                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->output->writeln($template . " minified\n");
                } else {
                    $this->output->write('.');
                }
                $count++;
            }
            $this->output->writeln("\nSuccessful: {$count} files modified\n---\n");
        }
    }

    /**
     * Save version of deployed files
     * @return void
     */
    public function saveDeployedVersion()
    {
        $version = (new \DateTime())->getTimestamp();
        $this->output->writeln("New version of deployed files: {$version}");
        if (isset($this->options[Options::DRY_RUN]) && !$this->options[Options::DRY_RUN]) {
            $this->versionStorage->save($version);
        }
    }
}
