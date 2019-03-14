<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\File\Transfer\Adapter;

class Http
{
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Response
     */
    private $response;

    /**
     * @var \Magento\Framework\File\Mime
     */
    private $mime;

    /**
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Framework\File\Mime $mime
     */
    public function __construct(
        \Magento\Framework\HTTP\PhpEnvironment\Response $response,
        \Magento\Framework\File\Mime $mime
    ) {
        $this->response = $response;
        $this->mime = $mime;
    }

    /**
     * Send the file to the client (Download)
     *
     * @param  string|array $options Options for the file(s) to send
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @return void
     */
    public function send($options = null)
    {
        $filepath = $this->getFilePath($options);

        if (!is_file($filepath) || !is_readable($filepath)) {
            throw new \InvalidArgumentException("File '{$filepath}' does not exists.");
        }

        $mimeType = $this->mime->getMimeType($filepath);
        if (is_array($options) && isset($options['headers']) && $options['headers'] instanceof \Zend\Http\Headers) {
            $this->response->setHeaders($options['headers']);
        }
        $this->response->setHeader('Content-length', filesize($filepath));
        $this->response->setHeader('Content-Type', $mimeType);

        $this->response->sendHeaders();

        $handle = fopen($filepath, 'r');
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                echo $buffer;
            }
            if (!feof($handle)) {
                throw new \UnexpectedValueException("Unexpected end of file");
            }
            fclose($handle);
        }
    }

    /**
     * Get filepath by provided parameter $optons.
     * If the $options is a string it assumes it's a file path. If the option is an array method will look for the
     * 'filepath' key and return it's value.
     *
     * @param string|array|null $options
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getFilePath($options): string
    {
        if (is_string($options)) {
            $filePath = $options;
        } elseif (is_array($options) && isset($options['filepath'])) {
            $filePath = $options['filepath'];
        } else {
            throw new \InvalidArgumentException("Filename is not set.");
        }

        return $filePath;
    }
}
