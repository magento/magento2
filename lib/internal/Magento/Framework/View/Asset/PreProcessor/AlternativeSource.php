<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\LockerProcessInterface;
use Magento\Framework\View\Asset\ContentProcessorInterface;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSource\AssetBuilder;

/**
 * Class AlternativeSource
 */
class AlternativeSource implements AlternativeSourceInterface
{
    /**
     * The key name of the processor class
     */
    const PROCESSOR_CLASS = 'class';

    /**
     * @var Helper\SorterInterface
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
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param LockerProcessInterface $lockerProcess
     * @param Helper\SorterInterface $sorter
     * @param AssetBuilder $assetBuilder
     * @param $lockName
     * @param array $alternatives
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        LockerProcessInterface $lockerProcess,
        Helper\SorterInterface $sorter,
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
    }

    /**
     * @inheritdoc
     * @throws \UnexpectedValueException
     */
    public function process(Chain $chain)
    {
        try {
            $this->processContent($chain);
        } finally {
            $this->lockerProcess->unlockProcess();
        }
    }

    /**
     * @param Chain $chain
     * @throws \UnexpectedValueException
     */
    public function processContent(Chain $chain)
    {
        $content = $chain->getContent();
        /** @var  \Magento\Framework\View\Asset\File\FallbackContext $context */
        $context = $chain->getAsset()->getContext();
        $path = $chain->getAsset()->getFilePath();

        $this->lockerProcess->lockProcess($this->lockName . sprintf('%x', crc32($path . $content)));

        if (!isset($this->alternativesSorted)) {
            $this->alternativesSorted = $this->sorter->sorting($this->alternatives);
        }

        foreach ($this->alternativesSorted as $name => $alternative) {
            if (trim($content) !== '') {
                break;
            }

            $asset = $this->assetBuilder->setArea($context->getAreaCode())
                ->setTheme($context->getThemePath())
                ->setLocale($context->getLocale())
                ->setModule($chain->getAsset()->getModule())
                ->setPath(preg_replace(
                    '#\.' . preg_quote(pathinfo($chain->getAsset()->getFilePath(), PATHINFO_EXTENSION)) . '$#',
                    '.' . $name,
                    $path
                ))->build();

            $processor = $this->objectManager->get($alternative[self::PROCESSOR_CLASS]);
            if (!$processor  instanceof ContentProcessorInterface) {
                throw new \UnexpectedValueException(
                    '"' . $alternative[self::PROCESSOR_CLASS] . '" has to implement the PreProcessorInterface.'
                );
            }

            $content = $processor->processContent($asset);
            $chain->setContent($content);
        }
    }

    /**
     * @inheritdoc
     */
    public function getAlternatives()
    {
        return array_keys($this->alternatives);
    }
}
