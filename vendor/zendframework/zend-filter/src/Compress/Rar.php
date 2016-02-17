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
 * Compression adapter for Rar
 */
class Rar extends AbstractCompressionAlgorithm
{
    /**
     * Compression Options
     * array(
     *     'callback' => Callback for compression
     *     'archive'  => Archive to use
     *     'password' => Password to use
     *     'target'   => Target to write the files to
     * )
     *
     * @var array
     */
    protected $options = array(
        'callback' => null,
        'archive'  => null,
        'password' => null,
        'target'   => '.',
    );

    /**
     * Class constructor
     *
     * @param array $options (Optional) Options to set
     * @throws Exception\ExtensionNotLoadedException if rar extension not loaded
     */
    public function __construct($options = null)
    {
        if (!extension_loaded('rar')) {
            throw new Exception\ExtensionNotLoadedException('This filter needs the rar extension');
        }
        parent::__construct($options);
    }

    /**
     * Returns the set callback for compression
     *
     * @return string
     */
    public function getCallback()
    {
        return $this->options['callback'];
    }

    /**
     * Sets the callback to use
     *
     * @param  string $callback
     * @return self
     * @throws Exception\InvalidArgumentException if invalid callback provided
     */
    public function setCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception\InvalidArgumentException('Invalid callback provided');
        }

        $this->options['callback'] = $callback;
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
        $archive = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $archive);
        $this->options['archive'] = (string) $archive;

        return $this;
    }

    /**
     * Returns the set password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->options['password'];
    }

    /**
     * Sets the password to use
     *
     * @param  string $password
     * @return self
     */
    public function setPassword($password)
    {
        $this->options['password'] = (string) $password;
        return $this;
    }

    /**
     * Returns the set targetpath
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->options['target'];
    }

    /**
     * Sets the targetpath to use
     *
     * @param  string $target
     * @return self
     * @throws Exception\InvalidArgumentException if specified target directory does not exist
     */
    public function setTarget($target)
    {
        if (!file_exists(dirname($target))) {
            throw new Exception\InvalidArgumentException("The directory '$target' does not exist");
        }

        $target = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, (string) $target);
        $this->options['target'] = $target;
        return $this;
    }

    /**
     * Compresses the given content
     *
     * @param  string|array $content
     * @return string
     * @throws Exception\RuntimeException if no callback available, or error during compression
     */
    public function compress($content)
    {
        $callback = $this->getCallback();
        if ($callback === null) {
            throw new Exception\RuntimeException('No compression callback available');
        }

        $options = $this->getOptions();
        unset($options['callback']);

        $result = call_user_func($callback, $options, $content);
        if ($result !== true) {
            throw new Exception\RuntimeException('Error compressing the RAR Archive');
        }

        return $this->getArchive();
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return bool
     * @throws Exception\RuntimeException if archive not found, cannot be opened,
     *                                    or error during decompression
     */
    public function decompress($content)
    {
        if (!file_exists($content)) {
            throw new Exception\RuntimeException('RAR Archive not found');
        }

        $archive  = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, realpath($content));
        $password = $this->getPassword();
        if ($password !== null) {
            $archive = rar_open($archive, $password);
        } else {
            $archive = rar_open($archive);
        }

        if (!$archive) {
            throw new Exception\RuntimeException("Error opening the RAR Archive");
        }

        $target = $this->getTarget();
        if (!is_dir($target)) {
            $target = dirname($target);
        }

        $filelist = rar_list($archive);
        if (!$filelist) {
            throw new Exception\RuntimeException("Error reading the RAR Archive");
        }

        foreach ($filelist as $file) {
            $file->extract($target);
        }

        rar_close($archive);
        return true;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Rar';
    }
}
