<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Magento\Framework\App\View\Deployment\Version\StorageInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Deploy\Console\Command\DeployStaticOptionsInterface as Options;
use Magento\Deploy\Model\Deploy\TemplateMinifier;
use Magento\Framework\App\State;

class DeployManager
{
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
     * @var ProcessQueueManagerFactory
     */
    private $processQueueManagerFactory;

    /**
     * @var TemplateMinifier
     */
    private $templateMinifier;

    /**
     * @var bool
     */
    private $idDryRun;

    /**
     * @var State
     */
    private $state;

    /**
     * @param OutputInterface $output
     * @param StorageInterface $versionStorage
     * @param DeployStrategyProviderFactory $deployStrategyProviderFactory
     * @param ProcessQueueManagerFactory $processQueueManagerFactory
     * @param TemplateMinifier $templateMinifier
     * @param State $state
     * @param array $options
     */
    public function __construct(
        OutputInterface $output,
        StorageInterface $versionStorage,
        DeployStrategyProviderFactory $deployStrategyProviderFactory,
        ProcessQueueManagerFactory $processQueueManagerFactory,
        TemplateMinifier $templateMinifier,
        State $state,
        array $options
    ) {
        $this->output = $output;
        $this->options = $options;
        $this->versionStorage = $versionStorage;
        $this->deployStrategyProviderFactory = $deployStrategyProviderFactory;
        $this->processQueueManagerFactory = $processQueueManagerFactory;
        $this->templateMinifier = $templateMinifier;
        $this->state = $state;
        $this->idDryRun = !empty($this->options[Options::DRY_RUN]);
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
        if ($this->idDryRun) {
            $this->output->writeln('Dry run. Nothing will be recorded to the target directory.');
        } else {
            $version = (new \DateTime())->getTimestamp();
            $this->versionStorage->save($version);
        }

        /** @var DeployStrategyProvider $strategyProvider */
        $strategyProvider = $this->deployStrategyProviderFactory->create(
            ['output' => $this->output, 'options' => $this->options]
        );

        if ($this->isCanBeParalleled()) {
            $result = $this->runInParallel($strategyProvider);
        } else {
            $result = 0;
            foreach ($this->packages as $package) {
                $locales = array_keys($package);
                list($area, $themePath) = current($package);
                foreach ($strategyProvider->getDeployStrategies($area, $themePath, $locales) as $locale => $strategy) {
                    $result |= $this->state->emulateAreaCode(
                        $area,
                        [$strategy, 'deploy'],
                        [$area, $themePath, $locale]
                    );
                }
            }
        }

        $this->minifyTemplates();
        if (!$this->idDryRun) {
            $this->output->writeln("New version of deployed files: {$version}");
        }

        return $result;
    }

    /**
     * @return void
     */
    private function minifyTemplates()
    {
        $noHtmlMinify = isset($this->options[Options::NO_HTML_MINIFY]) ? $this->options[Options::NO_HTML_MINIFY] : null;
        if (!$noHtmlMinify && !$this->idDryRun) {
            $this->output->writeln('=== Minify templates ===');
            $minified = $this->templateMinifier->minifyTemplates();
            $this->output->writeln("\nSuccessful: {$minified} files modified\n---\n");
        }
    }

    /**
     * @param DeployStrategyProvider $strategyProvider
     * @return int
     */
    private function runInParallel($strategyProvider)
    {
        $processQueueManager = $this->processQueueManagerFactory->create(
            ['maxProcesses' => $this->getProcessesAmount()]
        );
        foreach ($this->packages as $package) {
            $locales = array_keys($package);
            list($area, $themePath) = current($package);
            $baseStrategy = null;
            $dependentStrategy = [];
            foreach ($strategyProvider->getDeployStrategies($area, $themePath, $locales) as $locale => $strategy) {
                $deploymentFunc = function () use ($area, $themePath, $locale, $strategy) {
                    return $this->state->emulateAreaCode($area, [$strategy, 'deploy'], [$area, $themePath, $locale]);
                };
                if (null === $baseStrategy) {
                    $baseStrategy = $deploymentFunc;
                } else {
                    $dependentStrategy[] = $deploymentFunc;
                }

            }
            $processQueueManager->addTaskToQueue($baseStrategy, $dependentStrategy);
        }

        return $processQueueManager->process();
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
}
