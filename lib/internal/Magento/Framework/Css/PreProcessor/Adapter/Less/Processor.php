<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\Adapter\Less;

use Magento\Framework\App\State;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Framework\Phrase;
use Magento\Framework\View\Asset\ContentProcessorException;
use Magento\Framework\View\Asset\ContentProcessorInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Source;
use Psr\Log\LoggerInterface;

/**
 * Class Processor
 * @since 2.0.0
 */
class Processor implements ContentProcessorInterface
{
    /**
     * @var LoggerInterface
     * @since 2.0.0
     */
    private $logger;

    /**
     * @var State
     * @since 2.0.0
     */
    private $appState;

    /**
     * @var Source
     * @since 2.0.0
     */
    private $assetSource;

    /**
     * @var Temporary
     * @since 2.0.0
     */
    private $temporaryFile;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param State $appState
     * @param Source $assetSource
     * @param Temporary $temporaryFile
     * @since 2.0.0
     */
    public function __construct(
        LoggerInterface $logger,
        State $appState,
        Source $assetSource,
        Temporary $temporaryFile
    ) {
        $this->logger = $logger;
        $this->appState = $appState;
        $this->assetSource = $assetSource;
        $this->temporaryFile = $temporaryFile;
    }

    /**
     * @inheritdoc
     * @throws ContentProcessorException
     * @since 2.0.0
     */
    public function processContent(File $asset)
    {
        $path = $asset->getPath();
        try {
            $parser = new \Less_Parser(
                [
                    'relativeUrls' => false,
                    'compress' => $this->appState->getMode() !== State::MODE_DEVELOPER
                ]
            );

            $content = $this->assetSource->getContent($asset);

            if (trim($content) === '') {
                return '';
            }

            $tmpFilePath = $this->temporaryFile->createFile($path, $content);

            gc_disable();
            $parser->parseFile($tmpFilePath, '');
            $content = $parser->getCss();
            gc_enable();

            if (trim($content) === '') {
                $this->logger->warning('Parsed less file is empty: ' . $path);
                return '';
            } else {
                return $content;
            }
        } catch (\Exception $e) {
            throw new ContentProcessorException(new Phrase($e->getMessage()));
        }
    }
}
