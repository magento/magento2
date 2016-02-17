<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\File;

use Zend\Validator\Exception;

/**
 * Validator for the excluding file extensions
 */
class ExcludeExtension extends Extension
{
    /**
     * @const string Error constants
     */
    const FALSE_EXTENSION = 'fileExcludeExtensionFalse';
    const NOT_FOUND       = 'fileExcludeExtensionNotFound';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::FALSE_EXTENSION => "File has an incorrect extension",
        self::NOT_FOUND       => "File is not readable or does not exist",
    );

    /**
     * Returns true if and only if the file extension of $value is not included in the
     * set extension list
     *
     * @param  string|array $value Real file to check for extension
     * @param  array        $file  File data from \Zend\File\Transfer\Transfer (optional)
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        if (is_string($value) && is_array($file)) {
            // Legacy Zend\Transfer API support
            $filename = $file['name'];
            $file     = $file['tmp_name'];
        } elseif (is_array($value)) {
            if (!isset($value['tmp_name']) || !isset($value['name'])) {
                throw new Exception\InvalidArgumentException(
                    'Value array must be in $_FILES format'
                );
            }
            $file     = $value['tmp_name'];
            $filename = $value['name'];
        } else {
            $file     = $value;
            $filename = basename($file);
        }
        $this->setValue($filename);

        // Is file readable ?
        if (empty($file) || false === stream_resolve_include_path($file)) {
            $this->error(self::NOT_FOUND);
            return false;
        }

        $extension  = substr($filename, strrpos($filename, '.') + 1);
        $extensions = $this->getExtension();

        if ($this->getCase() && (!in_array($extension, $extensions))) {
            return true;
        } elseif (!$this->getCase()) {
            foreach ($extensions as $ext) {
                if (strtolower($ext) == strtolower($extension)) {
                    $this->error(self::FALSE_EXTENSION);
                    return false;
                }
            }

            return true;
        }

        $this->error(self::FALSE_EXTENSION);
        return false;
    }
}
