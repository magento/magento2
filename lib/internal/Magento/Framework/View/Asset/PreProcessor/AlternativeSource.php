<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\LockerProcessInterface;
use Magento\Framework\View\Asset\ContentProcessorInterface;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSource\AssetBuilder;

/**
 * Class AlternativeSource
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class AlternativeSource implements AlternativeSourceInterface
{
    /**
     * The key name of the processor class
     */
    const PROCESSOR_CLASS = 'class';

    /**
     * @var Helper\SortInterface
     * @since 2.0.0
     */
    private $sorter;

    /**
     * @var array
     * @since 2.0.0
     */
    private $alternatives;

    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @var array
     * @since 2.0.0
     */
    private $alternativesSorted;

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
     * @var AssetBuilder
     * @since 2.0.0
     */
    private $assetBuilder;

    /**
     * @var FilenameResolverInterface
     * @since 2.0.0
     */
    private $filenameResolver;

    /**
     * Constructor
     *
     * @param FilenameResolverInterface $filenameResolver
     * @param ObjectManagerInterface $objectManager
     * @param LockerProcessInterface $lockerProcess
     * @param Helper\SortInterface $sorter
     * @param AssetBuilder $assetBuilder
     * @param string $lockName
     * @param array $alternatives
     * @since 2.0.0
     */
    public function __construct(
        FilenameResolverInterface $filenameResolver,
        ObjectManagerInterface $objectManager,
        LockerProcessInterface $lockerProcess,
        Helper\SortInterface $sorter,
        AssetBuilder $assetBuilder,
        $lockName,
        array $alternatives = []
    ) {
        $this->objectManager = $objectManager;
        $this->lockerProcess = $lockerProcess;
        $this->sorter = $sorter;
        $this->alternatives = $alternatives;
        $this->lockName = $lockName;
        $this->assetBuilder = $assetBuilder;
        $this->filenameResolver = $filenameResolver;
    }

    /**
     * @inheritdoc
     * @throws \UnexpectedValueException
     * @since 2.0.0
     */
    public function process(Chain $chain)
    {
        $path = $chain->getAsset()->getFilePath();
        $content = $chain->getContent();
        if (trim($content) !== '') {
            return;
        }

        try {
            $this->lockerProcess->lockProcess($this->lockName);

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
     * @throws \UnexpectedValueException
     * @since 2.0.0
     */
    private function processContent($path, $content, $module, FallbackContext $context)
    {
        if ($this->alternativesSorted === null) {
            $this->alternativesSorted = $this->sorter->sort($this->alternatives);
        }

        $path = $this->filenameResolver->resolve($path);
        foreach ($this->alternativesSorted as $name => $alternative) {
            $asset = $this->assetBuilder->setArea($context->getAreaCode())
                ->setTheme($context->getThemePath())
                ->setLocale($context->getLocale())
                ->setModule($module)
                ->setPath(preg_replace(
                    '#\.' . preg_quote(pathinfo($path, PATHINFO_EXTENSION)) . '$#',
                    '.' . $name,
                    $path
                ))->build();

            $processor = $this->objectManager->get($alternative[self::PROCESSOR_CLASS]);
            if (!$processor  instanceof ContentProcessorInterface) {
                throw new \UnexpectedValueException(
                    '"' . $alternative[self::PROCESSOR_CLASS] . '" has to implement the ContentProcessorInterface.'
                );
            }
            $content = $processor->processContent($asset);

            if (trim($content) !== '') {
                return $content;
            }
        }

        return $content;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getAlternativesExtensionsNames()
    {
        return array_keys($this->alternatives);
    }
}
