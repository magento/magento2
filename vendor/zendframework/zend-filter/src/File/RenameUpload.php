<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter\File;

use Zend\Filter\AbstractFilter;
use Zend\Filter\Exception;
use Zend\Stdlib\ErrorHandler;

class RenameUpload extends AbstractFilter
{
    /**
     * @var array
     */
    protected $options = array(
        'target'               => null,
        'use_upload_name'      => false,
        'use_upload_extension' => false,
        'overwrite'            => false,
        'randomize'            => false,
    );

    /**
     * Store already filtered values, so we can filter multiple
     * times the same file without being block by move_uploaded_file
     * internal checks
     *
     * @var array
     */
    protected $alreadyFiltered = array();

    /**
     * Constructor
     *
     * @param array|string $targetOrOptions The target file path or an options array
     */
    public function __construct($targetOrOptions)
    {
        if (is_array($targetOrOptions)) {
            $this->setOptions($targetOrOptions);
        } else {
            $this->setTarget($targetOrOptions);
        }
    }

    /**
     * @param  string $target Target file path or directory
     * @return self
     */
    public function setTarget($target)
    {
        if (!is_string($target)) {
            throw new Exception\InvalidArgumentException(
                'Invalid target, must be a string'
            );
        }
        $this->options['target'] = $target;
        return $this;
    }

    /**
     * @return string Target file path or directory
     */
    public function getTarget()
    {
        return $this->options['target'];
    }

    /**
     * @param  bool $flag When true, this filter will use the $_FILES['name']
     *                       as the target filename.
     *                       Otherwise, it uses the default 'target' rules.
     * @return self
     */
    public function setUseUploadName($flag = true)
    {
        $this->options['use_upload_name'] = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUseUploadName()
    {
        return $this->options['use_upload_name'];
    }

    /**
     * @param  bool $flag When true, this filter will use the original file
     *                    extension for the target filename
     * @return self
     */
    public function setUseUploadExtension($flag = true)
    {
        $this->options['use_upload_extension'] = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUseUploadExtension()
    {
        return $this->options['use_upload_extension'];
    }

    /**
     * @param  bool $flag Shall existing files be overwritten?
     * @return self
     */
    public function setOverwrite($flag = true)
    {
        $this->options['overwrite'] = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getOverwrite()
    {
        return $this->options['overwrite'];
    }

    /**
     * @param  bool $flag Shall target files have a random postfix attached?
     * @return self
     */
    public function setRandomize($flag = true)
    {
        $this->options['randomize'] = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRandomize()
    {
        return $this->options['randomize'];
    }

    /**
     * Defined by Zend\Filter\Filter
     *
     * Renames the file $value to the new name set before
     * Returns the file $value, removing all but digit characters
     *
     * @param  string|array $value Full path of file to change or $_FILES data array
     * @throws Exception\RuntimeException
     * @return string|array The new filename which has been set, or false when there were errors
     */
    public function filter($value)
    {
        if (!is_scalar($value) && !is_array($value)) {
            return $value;
        }

        // An uploaded file? Retrieve the 'tmp_name'
        $isFileUpload = false;
        if (is_array($value)) {
            if (!isset($value['tmp_name'])) {
                return $value;
            }

            $isFileUpload = true;
            $uploadData = $value;
            $sourceFile = $value['tmp_name'];
        } else {
            $uploadData = array(
                'tmp_name' => $value,
                'name'     => $value,
            );
            $sourceFile = $value;
        }

        if (isset($this->alreadyFiltered[$sourceFile])) {
            return $this->alreadyFiltered[$sourceFile];
        }

        $targetFile = $this->getFinalTarget($uploadData);
        if (!file_exists($sourceFile) || $sourceFile == $targetFile) {
            return $value;
        }

        $this->checkFileExists($targetFile);
        $this->moveUploadedFile($sourceFile, $targetFile);

        $return = $targetFile;
        if ($isFileUpload) {
            $return = $uploadData;
            $return['tmp_name'] = $targetFile;
        }

        $this->alreadyFiltered[$sourceFile] = $return;

        return $return;
    }

    /**
     * @param  string $sourceFile Source file path
     * @param  string $targetFile Target file path
     * @throws Exception\RuntimeException
     * @return bool
     */
    protected function moveUploadedFile($sourceFile, $targetFile)
    {
        ErrorHandler::start();
        $result = move_uploaded_file($sourceFile, $targetFile);
        $warningException = ErrorHandler::stop();
        if (!$result || null !== $warningException) {
            throw new Exception\RuntimeException(
                sprintf("File '%s' could not be renamed. An error occurred while processing the file.", $sourceFile),
                0,
                $warningException
            );
        }

        return $result;
    }

    /**
     * @param  string $targetFile Target file path
     * @throws Exception\InvalidArgumentException
     */
    protected function checkFileExists($targetFile)
    {
        if (file_exists($targetFile)) {
            if ($this->getOverwrite()) {
                unlink($targetFile);
            } else {
                throw new Exception\InvalidArgumentException(
                    sprintf("File '%s' could not be renamed. It already exists.", $targetFile)
                );
            }
        }
    }

    /**
     * @param  array $uploadData $_FILES array
     * @return string
     */
    protected function getFinalTarget($uploadData)
    {
        $source = $uploadData['tmp_name'];
        $target = $this->getTarget();
        if (!isset($target) || $target == '*') {
            $target = $source;
        }

        // Get the target directory
        if (is_dir($target)) {
            $targetDir = $target;
            $last      = $target[strlen($target) - 1];
            if (($last != '/') && ($last != '\\')) {
                $targetDir .= DIRECTORY_SEPARATOR;
            }
        } else {
            $info      = pathinfo($target);
            $targetDir = $info['dirname'] . DIRECTORY_SEPARATOR;
        }

        // Get the target filename
        if ($this->getUseUploadName()) {
            $targetFile = basename($uploadData['name']);
        } elseif (!is_dir($target)) {
            $targetFile = basename($target);
            if ($this->getUseUploadExtension() && !$this->getRandomize()) {
                $targetInfo = pathinfo($targetFile);
                $sourceinfo = pathinfo($uploadData['name']);
                if (isset($sourceinfo['extension'])) {
                    $targetFile = $targetInfo['filename'] . '.' . $sourceinfo['extension'];
                }
            }
        } else {
            $targetFile = basename($source);
        }

        if ($this->getRandomize()) {
            $targetFile = $this->applyRandomToFilename($uploadData['name'], $targetFile);
        }

        return $targetDir . $targetFile;
    }

    /**
     * @param  string $source
     * @param  string $filename
     * @return string
     */
    protected function applyRandomToFilename($source, $filename)
    {
        $info = pathinfo($filename);
        $filename = $info['filename'] . uniqid('_');

        $sourceinfo = pathinfo($source);

        $extension = '';
        if ($this->getUseUploadExtension() === true && isset($sourceinfo['extension'])) {
            $extension .= '.' . $sourceinfo['extension'];
        } elseif (isset($info['extension'])) {
            $extension .= '.' . $info['extension'];
        }

        return $filename . $extension;
    }
}
