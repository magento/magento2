<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\PreProcessor\Adapter\Less;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
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
 *
 * Process LESS files into CSS
 */
class Processor implements ContentProcessorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var Source
     */
    private $assetSource;

    /**
     * @var Temporary
     */
    private $temporaryFile;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param State $appState
     * @param Source $assetSource
     * @param Temporary $temporaryFile
     * @param ?DirectoryList $directoryList
     */
    public function __construct(
        LoggerInterface $logger,
        State $appState,
        Source $assetSource,
        Temporary $temporaryFile,
        ?DirectoryList $directoryList = null,
    ) {
        $this->logger = $logger;
        $this->appState = $appState;
        $this->assetSource = $assetSource;
        $this->temporaryFile = $temporaryFile;
        $this->directoryList = $directoryList ?: ObjectManager::getInstance()->get(DirectoryList::class);
    }

    /**
     * @inheritdoc
     */
    public function processContent(File $asset)
    {
        $path = $asset->getPath();
        try {
            $mode = $this->appState->getMode();
            $sourceMapBasePath = sprintf(
                '%s/pub/',
                $this->directoryList->getPath(DirectoryList::TEMPLATE_MINIFICATION_DIR),
            );

            $parser = new \Less_Parser(
                [
                    'relativeUrls' => false,
                    'compress' => $mode !== State::MODE_DEVELOPER,
                    'sourceMap' => $mode === State::MODE_DEVELOPER,
                    'sourceMapRootpath' => '/',
                    'sourceMapBasepath' => $sourceMapBasePath,
                ]
            );

            $content = $this->assetSource->getContent($asset);

            if (trim($content) === '') {
                throw new ContentProcessorException(
                    new Phrase('Compilation from source: LESS file is empty: ' . $path)
                );
            }

            $tmpFilePath = $this->temporaryFile->createFile($path, $content);

            gc_disable();
            $parser->parseFile($tmpFilePath, '');
            $content = $parser->getCss();
            gc_enable();

            if (trim($content) === '') {
                throw new ContentProcessorException(
                    new Phrase('Compilation from source: LESS file is empty: ' . $path)
                );
            } else {
                return $content;
            }
        } catch (\Exception $e) {
            throw new ContentProcessorException(new Phrase($e->getMessage()));
        }
    }
}
