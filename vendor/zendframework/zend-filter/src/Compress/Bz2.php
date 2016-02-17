<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter\Compress;

use Zend\Filter\Exception;

/**
 * Compression adapter for Bz2
 */
class Bz2 extends AbstractCompressionAlgorithm
{
    /**
     * Compression Options
     * array(
     *     'blocksize' => Blocksize to use from 0-9
     *     'archive'   => Archive to use
     * )
     *
     * @var array
     */
    protected $options = array(
        'blocksize' => 4,
        'archive'   => null,
    );

    /**
     * Class constructor
     *
     * @param null|array|\Traversable $options (Optional) Options to set
     * @throws Exception\ExtensionNotLoadedException if bz2 extension not loaded
     */
    public function __construct($options = null)
    {
        if (!extension_loaded('bz2')) {
            throw new Exception\ExtensionNotLoadedException('This filter needs the bz2 extension');
        }
        parent::__construct($options);
    }

    /**
     * Returns the set blocksize
     *
     * @return int
     */
    public function getBlocksize()
    {
        return $this->options['blocksize'];
    }

    /**
     * Sets a new blocksize
     *
     * @param  int $blocksize
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function setBlocksize($blocksize)
    {
        if (($blocksize < 0) || ($blocksize > 9)) {
            throw new Exception\InvalidArgumentException('Blocksize must be between 0 and 9');
        }

        $this->options['blocksize'] = (int) $blocksize;
        return $this;
    }

    /**
     * Returns the set archive
     *
     * @return string
     */
    public function getArchive()
    {
        return $this->options['archive'];
    }

    /**
     * Sets the archive to use for de-/compression
     *
     * @param  string $archive Archive to use
     * @return self
     */
    public function setArchive($archive)
    {
        $this->options['archive'] = (string) $archive;
        return $this;
    }

    /**
     * Compresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException
     */
    public function compress($content)
    {
        $archive = $this->getArchive();
        if (!empty($archive)) {
            $file = bzopen($archive, 'w');
            if (!$file) {
                throw new Exception\RuntimeException("Error opening the archive '" . $archive . "'");
            }

            bzwrite($file, $content);
            bzclose($file);
            $compressed = true;
        } else {
            $compressed = bzcompress($content, $this->getBlocksize());
        }

        if (is_int($compressed)) {
            throw new Exception\RuntimeException('Error during compression');
        }

        return $compressed;
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException
     */
    public function decompress($content)
    {
        $archive = $this->getArchive();

        //check if there are null byte characters before doing a file_exists check
        if (!strstr($content, "\0") && file_exists($content)) {
            $archive = $content;
        }

        if (file_exists($archive)) {
            $file = bzopen($archive, 'r');
            if (!$file) {
                throw new Exception\RuntimeException("Error opening the archive '" . $content . "'");
            }

            $compressed = bzread($file);
            bzclose($file);
        } else {
            $compressed = bzdecompress($content);
        }

        if (is_int($compressed)) {
            throw new Exception\RuntimeException('Error during decompression');
        }

        return $compressed;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Bz2';
    }
}
