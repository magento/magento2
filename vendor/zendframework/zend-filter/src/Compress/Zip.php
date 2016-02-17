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
use ZipArchive;

/**
 * Compression adapter for zip
 */
class Zip extends AbstractCompressionAlgorithm
{
    /**
     * Compression Options
     * array(
     *     'archive'  => Archive to use
     *     'password' => Password to use
     *     'target'   => Target to write the files to
     * )
     *
     * @var array
     */
    protected $options = array(
        'archive' => null,
        'target'  => null,
    );

    /**
     * Class constructor
     *
     * @param  null|array|\Traversable $options (Optional) Options to set
     * @throws Exception\ExtensionNotLoadedException if zip extension not loaded
     */
    public function __construct($options = null)
    {
        if (!extension_loaded('zip')) {
            throw new Exception\ExtensionNotLoadedException('This filter needs the zip extension');
        }
        parent::__construct($options);
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
        $archive = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, (string) $archive);
        $this->options['archive'] = $archive;

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
     * Sets the target to use
     *
     * @param  string $target
     * @throws Exception\InvalidArgumentException
     * @return self
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
     * @param  string $content
     * @return string Compressed archive
     * @throws Exception\RuntimeException if unable to open zip archive, or error during compression
     */
    public function compress($content)
    {
        $zip = new ZipArchive();
        $res = $zip->open($this->getArchive(), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($res !== true) {
            throw new Exception\RuntimeException($this->errorString($res));
        }

        if (file_exists($content)) {
            $content  = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, realpath($content));
            $basename = substr($content, strrpos($content, DIRECTORY_SEPARATOR) + 1);
            if (is_dir($content)) {
                $index    = strrpos($content, DIRECTORY_SEPARATOR) + 1;
                $content .= DIRECTORY_SEPARATOR;
                $stack    = array($content);
                while (!empty($stack)) {
                    $current = array_pop($stack);
                    $files   = array();

                    $dir = dir($current);
                    while (false !== ($node = $dir->read())) {
                        if (($node == '.') || ($node == '..')) {
                            continue;
                        }

                        if (is_dir($current . $node)) {
                            array_push($stack, $current . $node . DIRECTORY_SEPARATOR);
                        }

                        if (is_file($current . $node)) {
                            $files[] = $node;
                        }
                    }

                    $local = substr($current, $index);
                    $zip->addEmptyDir(substr($local, 0, -1));

                    foreach ($files as $file) {
                        $zip->addFile($current . $file, $local . $file);
                        if ($res !== true) {
                            throw new Exception\RuntimeException($this->errorString($res));
                        }
                    }
                }
            } else {
                $res = $zip->addFile($content, $basename);
                if ($res !== true) {
                    throw new Exception\RuntimeException($this->errorString($res));
                }
            }
        } else {
            $file = $this->getTarget();
            if (!is_dir($file)) {
                $file = basename($file);
            } else {
                $file = "zip.tmp";
            }

            $res = $zip->addFromString($file, $content);
            if ($res !== true) {
                throw new Exception\RuntimeException($this->errorString($res));
            }
        }

        $zip->close();
        return $this->options['archive'];
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException If archive file not found, target directory not found,
     *                                    or error during decompression
     */
    public function decompress($content)
    {
        $archive = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, realpath($content));

        if (empty($archive) || !file_exists($archive)) {
            throw new Exception\RuntimeException('ZIP Archive not found');
        }

        $zip     = new ZipArchive();
        $res     = $zip->open($archive);

        $target = $this->getTarget();
        if (!empty($target) && !is_dir($target)) {
            $target = dirname($target);
        }

        if (!empty($target)) {
            $target = rtrim($target, '/\\') . DIRECTORY_SEPARATOR;
        }

        if (empty($target) || !is_dir($target)) {
            throw new Exception\RuntimeException('No target for ZIP decompression set');
        }

        if ($res !== true) {
            throw new Exception\RuntimeException($this->errorString($res));
        }

        $res = $zip->extractTo($target);
        if ($res !== true) {
            throw new Exception\RuntimeException($this->errorString($res));
        }

        $zip->close();
        return $target;
    }

    /**
     * Returns the proper string based on the given error constant
     *
     * @param  string $error
     * @return string
     */
    public function errorString($error)
    {
        switch ($error) {
            case ZipArchive::ER_MULTIDISK:
                return 'Multidisk ZIP Archives not supported';

            case ZipArchive::ER_RENAME:
                return 'Failed to rename the temporary file for ZIP';

            case ZipArchive::ER_CLOSE:
                return 'Failed to close the ZIP Archive';

            case ZipArchive::ER_SEEK:
                return 'Failure while seeking the ZIP Archive';

            case ZipArchive::ER_READ:
                return 'Failure while reading the ZIP Archive';

            case ZipArchive::ER_WRITE:
                return 'Failure while writing the ZIP Archive';

            case ZipArchive::ER_CRC:
                return 'CRC failure within the ZIP Archive';

            case ZipArchive::ER_ZIPCLOSED:
                return 'ZIP Archive already closed';

            case ZipArchive::ER_NOENT:
                return 'No such file within the ZIP Archive';

            case ZipArchive::ER_EXISTS:
                return 'ZIP Archive already exists';

            case ZipArchive::ER_OPEN:
                return 'Can not open ZIP Archive';

            case ZipArchive::ER_TMPOPEN:
                return 'Failure creating temporary ZIP Archive';

            case ZipArchive::ER_ZLIB:
                return 'ZLib Problem';

            case ZipArchive::ER_MEMORY:
                return 'Memory allocation problem while working on a ZIP Archive';

            case ZipArchive::ER_CHANGED:
                return 'ZIP Entry has been changed';

            case ZipArchive::ER_COMPNOTSUPP:
                return 'Compression method not supported within ZLib';

            case ZipArchive::ER_EOF:
                return 'Premature EOF within ZIP Archive';

            case ZipArchive::ER_INVAL:
                return 'Invalid argument for ZLIB';

            case ZipArchive::ER_NOZIP:
                return 'Given file is no zip archive';

            case ZipArchive::ER_INTERNAL:
                return 'Internal error while working on a ZIP Archive';

            case ZipArchive::ER_INCONS:
                return 'Inconsistent ZIP archive';

            case ZipArchive::ER_REMOVE:
                return 'Can not remove ZIP Archive';

            case ZipArchive::ER_DELETED:
                return 'ZIP Entry has been deleted';

            default:
                return 'Unknown error within ZIP Archive';
        }
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Zip';
    }
}
