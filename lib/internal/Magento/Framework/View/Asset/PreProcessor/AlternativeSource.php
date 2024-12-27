<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\ContentProcessorInterface;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\LockerProcessInterface;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSource\AssetBuilder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AlternativeSource implements AlternativeSourceInterface
{
    /**
     * The key name of the processor class
     */
    public const PROCESSOR_CLASS = 'class';

    /**
     * @var Helper\SortInterface
     */
    private $sorter;

    /**
     * @var array
     */
    private $alternatives;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $alternativesSorted;

    /**
     * @var LockerProcessInterface
     */
    private $lockerProcess;

    /**
     * @var string
     */
    private $lockName;

    /**
     * @var AssetBuilder
     */
    private $assetBuilder;

    /**
     * @var FilenameResolverInterface
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
     */
    public function process(Chain $chain)
    {
        $path = $chain->getAsset()->getFilePath();
        $content = $chain->getContent();
        if ($content && trim($content) !== '') {
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
                ->setPath(
                    preg_replace(
                        '#\.' . preg_quote(pathinfo($path, PATHINFO_EXTENSION)) . '$#',
                        '.' . $name,
                        $path
                    )
                )->build();

            $processor = $this->objectManager->get($alternative[self::PROCESSOR_CLASS]);
            if (!$processor  instanceof ContentProcessorInterface) {
                throw new \UnexpectedValueException(
                    '"' . $alternative[self::PROCESSOR_CLASS] . '" has to implement the ContentProcessorInterface.'
                );
            }
            $content = $processor->processContent($asset);

            if ($content && trim($content) !== '') {
                return $content;
            }
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function getAlternativesExtensionsNames()
    {
        return array_keys($this->alternatives);
    }

    /**
     * Check if file extension supported
     *
     * @param string $ext
     * @return bool
     */
    public function isExtensionSupported($ext)
    {
        return isset($this->alternatives[$ext]);
    }
}
