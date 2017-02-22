<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\PreProcessor;
use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\View\Asset\LockerProcessInterface;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSource\AssetBuilder;
use Magento\Framework\View\Asset\Source;

/**
 * Class FrontendCompilation
 */
class FrontendCompilation implements PreProcessorInterface
{
    /**
     * @var AlternativeSourceInterface
     */
    private $alternativeSource;

    /**
     * @var AssetBuilder
     */
    private $assetBuilder;

    /**
     * @var LockerProcessInterface
     */
    private $lockerProcess;

    /**
     * @var string
     */
    private $lockName;

    /**
     * @var Source
     */
    private $assetSource;

    /**
     * Constructor
     *
     * @param Source $assetSource
     * @param AssetBuilder $assetBuilder
     * @param AlternativeSourceInterface $alternativeSource
     * @param LockerProcessInterface $lockerProcess
     * @param string $lockName
     */
    public function __construct(
        Source $assetSource,
        AssetBuilder $assetBuilder,
        AlternativeSourceInterface $alternativeSource,
        LockerProcessInterface $lockerProcess,
        $lockName
    ) {
        $this->assetSource = $assetSource;
        $this->alternativeSource = $alternativeSource;
        $this->assetBuilder = $assetBuilder;
        $this->lockerProcess = $lockerProcess;
        $this->lockName = $lockName;
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param PreProcessor\Chain $chain
     * @return void
     */
    public function process(PreProcessor\Chain $chain)
    {
        $content = $chain->getContent();
        if (trim($content) !== '') {
            return;
        }

        try {
            $this->lockerProcess->lockProcess($this->lockName);

            $path = $chain->getAsset()->getFilePath();
            $module = $chain->getAsset()->getModule();

            /** @var FallbackContext $context */
            $context = $chain->getAsset()->getContext();
            $chain->setContent($this->processContent($path, $content, $module, $context));
        } finally {
            $this->lockerProcess->unlockProcess();
        }
    }

    /**
     * Preparation of content for the destination file
     *
     * @param string $path
     * @param string $content
     * @param string $module
     * @param FallbackContext $context
     * @return string
     */
    private function processContent($path, $content, $module, FallbackContext $context)
    {
        foreach ($this->alternativeSource->getAlternativesExtensionsNames() as $name) {
            $asset = $this->assetBuilder->setArea($context->getAreaCode())
                ->setTheme($context->getThemePath())
                ->setLocale($context->getLocale())
                ->setModule($module)
                ->setPath(preg_replace(
                    '#\.' . preg_quote(pathinfo($path, PATHINFO_EXTENSION)) . '$#',
                    '.' . $name,
                    $path
                ))->build();

            $processedContent = $this->assetSource->getContent($asset);

            if (trim($processedContent) !== '') {
                return $processedContent;
            }
        }

        return $content;
    }
}
