<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter\File;

use Zend\Filter;
use Zend\Filter\Exception;

/**
 * Decrypts a given file and stores the decrypted file content
 */
class Decrypt extends Filter\Decrypt
{
    /**
     * New filename to set
     *
     * @var string
     */
    protected $filename;

    /**
     * Returns the new filename where the content will be stored
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Sets the new filename where the content will be stored
     *
     * @param  string $filename (Optional) New filename to set
     * @return self
     */
    public function setFilename($filename = null)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Decrypts the file $value with the defined settings
     *
     * @param  string|array $value Full path of file to change or $_FILES data array
     * @return string|array The filename which has been set
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
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
            $value      = $value['tmp_name'];
        }

        if (!file_exists($value)) {
            throw new Exception\InvalidArgumentException("File '$value' not found");
        }

        if (!isset($this->filename)) {
            $this->filename = $value;
        }

        if (file_exists($this->filename) and !is_writable($this->filename)) {
            throw new Exception\RuntimeException("File '{$this->filename}' is not writable");
        }

        $content = file_get_contents($value);
        if (!$content) {
            throw new Exception\RuntimeException("Problem while reading file '$value'");
        }

        $decrypted = parent::filter($content);
        $result    = file_put_contents($this->filename, $decrypted);

        if (!$result) {
            throw new Exception\RuntimeException("Problem while writing file '{$this->filename}'");
        }

        if ($isFileUpload) {
            $uploadData['tmp_name'] = $this->filename;
            return $uploadData;
        }
        return $this->filename;
    }
}
