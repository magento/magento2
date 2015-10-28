<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\Adapter\Less;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Framework\View\Asset\ContentProcessorException;
use Magento\Framework\View\Asset\ContentProcessorInterface;

/**
 * Class Processor
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
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param State $appState
     * @param Source $assetSource
     * @param Temporary $temporaryFile
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
     */
    public function processContent(File $asset)
    {
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

            $tmpFilePath = $this->temporaryFile->createFile($asset->getPath(), $content);
            $parser->parseFile($tmpFilePath, '');

            $content = $parser->getCss();

            return $content;
        } catch (\Exception $e) {
            $errorMessage = self::ERROR_MESSAGE_PREFIX . $e->getMessage();
            $this->logger->critical($errorMessage);

            throw new ContentProcessorException($errorMessage);
        }
    }
}
