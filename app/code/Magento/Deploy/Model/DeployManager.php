<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Magento\Framework\App\View\Deployment\Version\StorageInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Deploy\Console\Command\DeployStaticOptionsInterface as Options;
use Magento\Deploy\Model\Deploy\TemplateMinifier;

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
     * @var StorageInterface
     */
    private $versionStorage;

    /**
     * @var DeployStrategyProviderFactory
     */
    private $deployStrategyProviderFactory;

    /**
     * @var ProcessQueueManager
     */
    private $processQueueManager;

    /**
     * @var \Magento\Deploy\Model\TemplateMinifier
     */
    private $templateMinifier;

    /**
     * @param OutputInterface $output
     * @param StorageInterface $versionStorage
     * @param DeployStrategyProviderFactory $deployStrategyProviderFactory
     * @param ProcessQueueManager $processQueueManager
     * @param TemplateMinifier $templateMinifier
     * @param array $options
     */
    public function __construct(
        OutputInterface $output,
        StorageInterface $versionStorage,
        DeployStrategyProviderFactory $deployStrategyProviderFactory,
        ProcessQueueManager $processQueueManager,
        TemplateMinifier $templateMinifier,
        array $options
    ) {
        $this->output = $output;
        $this->options = $options;
        $this->versionStorage = $versionStorage;
        $this->deployStrategyProviderFactory = $deployStrategyProviderFactory;
        $this->processQueueManager = $processQueueManager;
        $this->templateMinifier = $templateMinifier;
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

        /** @var DeployStrategyProvider $strategyProvider */
        $strategyProvider = $this->deployStrategyProviderFactory->create(
            ['output' => $this->output, 'options' => $this->options]
        );

        if ($this->isCanBeParalleled()) {
            $result = $this->runInParallel($strategyProvider);
        } else {
            $result = null;
            foreach ($this->packages as $package) {
                $locales = array_keys($package);
                list($area, $themePath) = current($package);
                foreach ($strategyProvider->getDeployStrategies($area, $themePath, $locales) as $locale => $strategy) {
                    $result |= $strategy->deploy($area, $themePath, $locale);
                }
            }
        }

        $this->minifyTemplates();
        $this->saveDeployedVersion();

        return $result;
    }

    /**
     * @return void
     */
    private function minifyTemplates()
    {
        $noHtmlMinify = isset($this->options[Options::NO_HTML_MINIFY]) ? $this->options[Options::NO_HTML_MINIFY] : null;
        if (!$noHtmlMinify) {
            $this->output->writeln('=== Minify templates ===');
            $minified = $this->templateMinifier->minifyTemplates();
            $this->output->writeln("\nSuccessful: {$minified} files modified\n---\n");
        }
    }

    /**
     * @param DeployStrategyProvider $strategyProvider
     * @return int
     */
    private function runInParallel(DeployStrategyProvider $strategyProvider)
    {
        $this->processQueueManager->setMaxProcessesAmount($this->getProcessesAmount());
        foreach ($this->packages as $package) {
            $locales = array_keys($package);
            list($area, $themePath) = current($package);
            $baseStrategy = null;
            $dependentStrategy = [];
            foreach ($strategyProvider->getDeployStrategies($area, $themePath, $locales) as $locale => $strategy) {
                $deploymentFunc = function () use ($area, $themePath, $locale, $strategy) {
                    return $strategy->deploy($area, $themePath, $locale);
                };
                if (null === $baseStrategy) {
                    $baseStrategy = $deploymentFunc;
                } else {
                    $dependentStrategy[] = $deploymentFunc;
                }

            }
            $this->processQueueManager->addTaskToQueue($baseStrategy, $dependentStrategy);
        }

        return $this->processQueueManager->process();
    }

    /**
     * @return bool
     */
    private function isCanBeParalleled()
    {
        return function_exists('pcntl_fork') && $this->getProcessesAmount() > 1;
    }

    /**
     * @return int
     */
    private function getProcessesAmount()
    {
        return isset($this->options[Options::JOBS_AMOUNT]) ? (int)$this->options[Options::JOBS_AMOUNT] : 0;
    }

    /**
     * Save version of deployed files
     * @return void
     */
    private function saveDeployedVersion()
    {
        $version = (new \DateTime())->getTimestamp();
        $this->output->writeln("New version of deployed files: {$version}");
        if (isset($this->options[Options::DRY_RUN]) && !$this->options[Options::DRY_RUN]) {
            $this->versionStorage->save($version);
        }
    }
}
