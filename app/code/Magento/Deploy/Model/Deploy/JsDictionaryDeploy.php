<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model\Deploy;

use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Translate\Js\Config as TranslationJsConfig;
use Magento\Framework\TranslateInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deploy class for js dictionary
 */
class JsDictionaryDeploy implements DeployInterface
{
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
     * @var OutputInterface
     */
    private $output;

    /**
     * @param Repository $assetRepo
     * @param Publisher $assetPublisher
     * @param TranslationJsConfig $translationJsConfig
     * @param TranslateInterface $translator
     * @param OutputInterface $output
     */
    public function __construct(
        Repository $assetRepo,
        Publisher $assetPublisher,
        TranslationJsConfig $translationJsConfig,
        TranslateInterface $translator,
        OutputInterface $output
    ) {
        $this->assetRepo = $assetRepo;
        $this->assetPublisher = $assetPublisher;
        $this->translationJsConfig = $translationJsConfig;
        $this->translator = $translator;
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function deploy($area, $themePath, $locale)
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

        return Cli::RETURN_SUCCESS;
    }
}
