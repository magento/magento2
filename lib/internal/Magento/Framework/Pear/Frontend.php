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

namespace Magento\Framework\Pear;

class Frontend
{
    /**
     * @var string|resource
     */
    protected $_logStream = null;

    /**
     * @var null
     */
    protected $_outStream = null;

    /**
     * @var string[]
     */
    protected $_log = array();

    /**
     * @var array
     */
    protected $_out = array();

    /**
     * Enter description here...
     *
     * @param string|resource $stream 'stdout' or open php stream
     * @return $this
     */
    public function setLogStream($stream)
    {
        $this->_logStream = $stream;
        return $this;
    }

    /**
     * @return string|resource
     */
    public function getLogStream()
    {
        return $this->_logStream;
    }

    /**
     * @param string $msg
     * @param bool $append_crlf
     * @return void
     */
    public function log($msg, $append_crlf = true)
    {
        if (is_null($msg) || false === $msg or '' === $msg) {
            return;
        }

        if ($append_crlf) {
            $msg .= "\r\n";
        }

        $this->_log[] = $msg;

        if ('stdout' === $this->_logStream) {
            if ($msg === '.') {
                echo ' ';
            }
            echo $msg;
        } elseif (is_resource($this->_logStream)) {
            fwrite($this->_logStream, $msg);
        }
    }

    /**
     * @param string|array $data
     * @param string $command
     * @return void
     */
    public function outputData($data, $command = '_default')
    {
        $this->_out[] = array('output' => $data, 'command' => $command);

        if ('stdout' === $this->_logStream) {
            if (is_string($data)) {
                echo $data . "\r\n";
            } elseif (is_array($data) && !empty($data['message']) && is_string($data['message'])) {
                echo $data['message'] . "\r\n";
            } elseif (is_array($data) && !empty($data['data']) && is_string($data['data'])) {
                echo $data['data'] . "\r\n";
            } else {
                print_r($data);
            }
        }
    }

    /**
     * @return void
     */
    public function userConfirm()
    {
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->_log = array();
        $this->_out = array();
    }

    /**
     * @return string[]
     */
    public function getLog()
    {
        return $this->_log;
    }

    /**
     * @return string
     */
    public function getLogText()
    {
        $text = '';
        foreach ($this->getLog() as $log) {
            $text .= $log;
        }
        return $text;
    }

    /**
     * @return array
     */
    public function getOutput()
    {
        return $this->_out;
    }
}
