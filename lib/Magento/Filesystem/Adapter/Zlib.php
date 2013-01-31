<?php
/**
 * Adapter for local compressed filesystem
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Filesystem_Adapter_Zlib extends Magento_Filesystem_Adapter_Local
{
    /**
     * @var int
     */
    protected $_compressRatio;

    /**
     * @var string
     */
    protected $_strategy;

    /**
     * @var null|bool
     */
    protected $_hasCompression = null;

    /**
     * Initialize Zlib adapter.
     *
     * @param int $ratio
     * @param string $strategy
     */
    public function __construct($ratio = 1, $strategy = '')
    {
        $this->_compressRatio = $ratio;
        $this->_strategy = $strategy;
    }

    /**
     * Read compressed file file
     *
     * @param string $key
     * @return string
     * @throws Magento_Filesystem_Exception
     */
    public function read($key)
    {
        $stream = $this->createStream($key);
        $stream->open('rb');

        $info = unpack("lcompress/llength", $stream->read(8));

        $compressed = (bool)$info['compress'];
        if ($compressed && !$this->_isCompressionAvailable()) {
            $stream->close();
            throw new Magento_Filesystem_Exception('The file was compressed, but zlib extension is not installed.');
        }
        if ($compressed) {
            $content = gzuncompress($stream->read($info['length']));
        } else {
            $content = $stream->read($info['length']);
        }

        $stream->close();
        return $content;
    }

    /**
     * Write compressed file.
     *
     * @param string $key
     * @param string $content
     * @return bool
     */
    public function write($key, $content)
    {
        $compress = $this->_isCompressionAvailable();
        if ($compress) {
            $rawContent = gzcompress($content, $this->_compressRatio);
        } else {
            $rawContent = $content;
        }

        $fileHeaders = pack("ll", (int)$compress, strlen($rawContent));
        return parent::write($key, $fileHeaders . $rawContent);
    }

    /**
     * Create Zlib stream
     *
     * @param string $path
     * @return Magento_Filesystem_Stream_Zlib
     */
    public function createStream($path)
    {
        return new Magento_Filesystem_Stream_Zlib($path);
    }

    /**
     * Check that zlib extension loaded.
     *
     * @return bool
     */
    protected function _isCompressionAvailable()
    {
        if ($this->_hasCompression === null) {
            $this->_hasCompression = extension_loaded("zlib");
        }
        return $this->_hasCompression;
    }
}
