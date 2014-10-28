<?php
/**
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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\File\Transfer\Adapter;

class Http
{
    /**
     * @var \Zend_Controller_Response_Http
     */
    private $response;

    /**
     * @var \Magento\Framework\File\Mime
     */
    private $mime;

    /**
     * @param \Magento\Framework\Controller\Response\Http $response
     * @param \Magento\Framework\File\Mime $mime
     */
    public function __construct(\Magento\Framework\Controller\Response\Http $response, \Magento\Framework\File\Mime $mime)
    {
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
        if (is_string($options)) {
            $filepath = $options;
        } else if (is_array($options) && isset($options['filepath'])) {
            $filepath = $options['filepath'];
        } else {
            throw new \InvalidArgumentException("Filename is not set.");
        }

        if (!is_file($filepath) || !is_readable($filepath)) {
            throw new \InvalidArgumentException("File '{$filepath}' does not exists.");
        }

        $mimeType = $this->mime->getMimeType($filepath);

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
}
