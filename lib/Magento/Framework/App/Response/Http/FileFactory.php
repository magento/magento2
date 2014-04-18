<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App\Response\Http;

class FileFactory
{
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $_filesystem;

    /**
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\App\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\Filesystem $filesystem
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
     */
    public function create(
        $fileName,
        $content,
        $baseDir = \Magento\Framework\App\Filesystem::ROOT_DIR,
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
                $contentLength = $dir->stat($file)['size'];
            }
        }

        $this->_response->setHttpResponseCode(
            200
        )->setHeader(
            'Pragma',
            'public',
            true
        )->setHeader(
            'Cache-Control',
            'must-revalidate, post-check=0, pre-check=0',
            true
        )->setHeader(
            'Content-type',
            $contentType,
            true
        )->setHeader(
            'Content-Length',
            is_null($contentLength) ? strlen($content) : $contentLength,
            true
        )->setHeader(
            'Content-Disposition',
            'attachment; filename="' . $fileName . '"',
            true
        )->setHeader(
            'Last-Modified',
            date('r'),
            true
        );

        if (!is_null($content)) {
            if ($isFile) {
                if (!$dir->isFile($file)) {
                    throw new \Exception(__('File not found'));
                }
                $this->_response->sendHeaders();
                $stream = $dir->openFile($file, 'r');
                while (!$stream->eof()) {
                    echo $stream->read(1024);
                }
                $stream->close();
                flush();
                if (!empty($content['rm'])) {
                    $dir->delete($file);
                }
                exit(0);
            } else {
                $this->_response->clearBody();
                $this->_response->setBody($content);
            }
        }
        return $this->_response;
    }
}
