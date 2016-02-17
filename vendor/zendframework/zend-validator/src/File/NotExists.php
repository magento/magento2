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
 * Validator which checks if the destination file does not exist
 */
class NotExists extends Exists
{
    /**
     * @const string Error constants
     */
    const DOES_EXIST = 'fileNotExistsDoesExist';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::DOES_EXIST => "File exists",
    );

    /**
     * Returns true if and only if the file does not exist in the set destinations
     *
     * @param  string|array $value Real file to check for existence
     * @param  array        $file  File data from \Zend\File\Transfer\Transfer (optional)
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        if (is_string($value) && is_array($file)) {
            // Legacy Zend\Transfer API support
            $filename = $file['name'];
            $file     = $file['tmp_name'];
            $this->setValue($filename);
        } elseif (is_array($value)) {
            if (!isset($value['tmp_name']) || !isset($value['name'])) {
                throw new Exception\InvalidArgumentException(
                    'Value array must be in $_FILES format'
                );
            }
            $file     = $value['tmp_name'];
            $filename = basename($file);
            $this->setValue($value['name']);
        } else {
            $file     = $value;
            $filename = basename($file);
            $this->setValue($filename);
        }

        $check = false;
        $directories = $this->getDirectory(true);
        if (!isset($directories)) {
            $check = true;
            if (file_exists($file)) {
                $this->error(self::DOES_EXIST);
                return false;
            }
        } else {
            foreach ($directories as $directory) {
                if (!isset($directory) || '' === $directory) {
                    continue;
                }

                $check = true;
                if (file_exists($directory . DIRECTORY_SEPARATOR . $filename)) {
                    $this->error(self::DOES_EXIST);
                    return false;
                }
            }
        }

        if (!$check) {
            $this->error(self::DOES_EXIST);
            return false;
        }

        return true;
    }
}
