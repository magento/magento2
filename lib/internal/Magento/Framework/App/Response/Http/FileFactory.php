<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Response\Http;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem;

/**
 * Class FileFactory serves to declare file content in response for download.
 *
 * @api
 */
class FileFactory
{
    /**
     * @deprecated
     * @see $fileResponseFactory
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\App\Response\FileFactory
     */
    private $fileResponseFactory;

    /**
     * @param ResponseInterface $response
     * @param Filesystem $filesystem
     * @param \Magento\Framework\App\Response\FileFactory|null $fileResponseFactory
     */
    public function __construct(
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Filesystem $filesystem,
        ?\Magento\Framework\App\Response\FileFactory $fileResponseFactory = null
    ) {
        $this->_response = $response;
        $this->_filesystem = $filesystem;
        $this->fileResponseFactory = $fileResponseFactory
            ?? ObjectManager::getInstance()->get(\Magento\Framework\App\Response\FileFactory::class);
    }

    /**
     * Declare headers and content file in response for file download
     *
     * @param string $fileName
     * @param string|array $content set to null to avoid starting output, $contentLength should be set explicitly in
     *                              that case
     * @param string $baseDir
     * @param string $contentType
     * @param int $contentLength explicit content length, if strlen($content) isn't applicable
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\App\ResponseInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function create(
        $fileName,
        $content,
        $baseDir = DirectoryList::ROOT,
        $contentType = 'application/octet-stream',
        $contentLength = null
    ) {
        $dir = $this->_filesystem->getDirectoryWrite($baseDir);
        $isFile = false;
        $file = null;
        $fileContent = $this->getFileContent($content);
        if (is_array($content)) {
            if (!isset($content['type']) || !isset($content['value'])) {
                throw new \InvalidArgumentException("Invalid arguments. Keys 'type' and 'value' are required.");
            }
            if ($content['type'] == 'filename') {
                $isFile = true;
                $file = $content['value'];
                if (!$dir->isFile($file)) {
                    // phpcs:ignore Magento2.Exceptions.DirectThrow
                    throw new \Exception((string)new \Magento\Framework\Phrase('File not found'));
                }
                $contentLength = $dir->stat($file)['size'];
            }
        }

        if ($content !== null) {
            if (!$isFile) {
                $dir->writeFile($fileName, $fileContent);
                $file = $fileName;
            }
        }
        return $this->fileResponseFactory->create([
            'options' => [
                'filePath' => $file,
                'fileName' => $fileName,
                'contentType' => $contentType,
                'contentLength' => $contentLength,
                'directoryCode' => $baseDir,
                'remove' => is_array($content) && !empty($content['rm'])
            ]
        ]);
    }

    /**
     * Returns file content for writing.
     *
     * @param string|array $content
     * @return string|array
     */
    private function getFileContent($content)
    {
        if (isset($content['type']) && $content['type'] === 'string') {
            return $content['value'];
        }

        return $content;
    }
}
