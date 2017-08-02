<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\Http;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class \Magento\Framework\App\Response\Http\FileFactory
 *
 * @since 2.0.0
 */
class FileFactory
{
    /**
     * @var \Magento\Framework\App\ResponseInterface
     * @since 2.0.0
     */
    protected $_response;

    /**
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    protected $_filesystem;

    /**
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\Filesystem $filesystem
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_response = $response;
        $this->_filesystem = $filesystem;
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
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @since 2.0.0
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
        if (is_array($content)) {
            if (!isset($content['type']) || !isset($content['value'])) {
                throw new \InvalidArgumentException("Invalid arguments. Keys 'type' and 'value' are required.");
            }
            if ($content['type'] == 'filename') {
                $isFile = true;
                $file = $content['value'];
                if (!$dir->isFile($file)) {
                    throw new \Exception((string)new \Magento\Framework\Phrase('File not found'));
                }
                $contentLength = $dir->stat($file)['size'];
            }
        }
        $this->_response->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', $contentLength === null ? strlen($content) : $contentLength, true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true)
            ->setHeader('Last-Modified', date('r'), true);

        if ($content !== null) {
            $this->_response->sendHeaders();
            if ($isFile) {
                $stream = $dir->openFile($file, 'r');
                while (!$stream->eof()) {
                    echo $stream->read(1024);
                }
            } else {
                $dir->writeFile($fileName, $content);
                $stream = $dir->openFile($fileName, 'r');
                while (!$stream->eof()) {
                    echo $stream->read(1024);
                }
            }
            $stream->close();
            flush();
            if (!empty($content['rm'])) {
                $dir->delete($file);
            }
            $this->callExit();
        }
        return $this->_response;
    }

    /**
     * Call exit
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @since 2.0.0
     */
    protected function callExit()
    {
        exit(0);
    }
}
