<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 *
 * @api
 * @since 2.0.0
 */
class FrontendCompilation implements PreProcessorInterface
{
    /**
     * @var AlternativeSourceInterface
     * @since 2.0.0
     */
    private $alternativeSource;

    /**
     * @var AssetBuilder
     * @since 2.0.0
     */
    private $assetBuilder;

    /**
     * @var LockerProcessInterface
     * @since 2.0.0
     */
    private $lockerProcess;

    /**
     * @var string
     * @since 2.0.0
     */
    private $lockName;

    /**
     * @var Source
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function process(PreProcessor\Chain $chain)
    {

        try {
            $this->lockerProcess->lockProcess($this->lockName);

            $path = $chain->getAsset()->getFilePath();
            $module = $chain->getAsset()->getModule();

            /** @var FallbackContext $context */
            $context = $chain->getAsset()->getContext();

            $result = $this->processContent($path, $chain->getContent(), $module, $context);
            $chain->setContent($result['content']);
            $chain->setContentType($result['sourceType']);
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
     * @return array
     * @since 2.0.0
     */
    private function processContent($path, $content, $module, FallbackContext $context)
    {
        $sourceTypePattern = '#\.' . preg_quote(pathinfo($path, PATHINFO_EXTENSION), '#') . '$#';

        foreach ($this->alternativeSource->getAlternativesExtensionsNames() as $name) {
            $asset = $this->assetBuilder->setArea($context->getAreaCode())
                ->setTheme($context->getThemePath())
                ->setLocale($context->getLocale())
                ->setModule($module)
                ->setPath(preg_replace($sourceTypePattern, '.' . $name, $path))
                ->build();

            $processedContent = $this->assetSource->getContent($asset);

            if (trim($processedContent) !== '') {
                return [
                    'content' => $processedContent,
                    'sourceType' => $name
                ];
            }
        }

        return [
            'content' => $content,
            'sourceType' => pathinfo($path, PATHINFO_EXTENSION)
        ];
    }
}
