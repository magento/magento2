<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\File;

use finfo;
use Zend\Validator\Exception;

/**
 * Validator for the mime type of a file
 */
class ExcludeMimeType extends MimeType
{
    const FALSE_TYPE   = 'fileExcludeMimeTypeFalse';
    const NOT_DETECTED = 'fileExcludeMimeTypeNotDetected';
    const NOT_READABLE = 'fileExcludeMimeTypeNotReadable';

    /**
     * Returns true if the mimetype of the file does not matche the given ones. Also parts
     * of mimetypes can be checked. If you give for example "image" all image
     * mime types will not be accepted like "image/gif", "image/jpeg" and so on.
     *
     * @param  string|array $value Real file to check for mimetype
     * @param  array        $file  File data from \Zend\File\Transfer\Transfer (optional)
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        if (is_string($value) && is_array($file)) {
            // Legacy Zend\Transfer API support
            $filename = $file['name'];
            $filetype = $file['type'];
            $file     = $file['tmp_name'];
        } elseif (is_array($value)) {
            if (!isset($value['tmp_name']) || !isset($value['name']) || !isset($value['type'])) {
                throw new Exception\InvalidArgumentException(
                    'Value array must be in $_FILES format'
                );
            }
            $file     = $value['tmp_name'];
            $filename = $value['name'];
            $filetype = $value['type'];
        } else {
            $file     = $value;
            $filename = basename($file);
            $filetype = null;
        }
        $this->setValue($filename);

        // Is file readable ?
        if (empty($file) || false === stream_resolve_include_path($file)) {
            $this->error(self::NOT_READABLE);
            return false;
        }

        $mimefile = $this->getMagicFile();
        if (class_exists('finfo', false)) {
            if (!$this->isMagicFileDisabled() && (!empty($mimefile) && empty($this->finfo))) {
                $this->finfo = finfo_open(FILEINFO_MIME_TYPE, $mimefile);
            }

            if (empty($this->finfo)) {
                $this->finfo = finfo_open(FILEINFO_MIME_TYPE);
            }

            $this->type = null;
            if (!empty($this->finfo)) {
                $this->type = finfo_file($this->finfo, $file);
            }
        }

        if (empty($this->type) && $this->getHeaderCheck()) {
            $this->type = $filetype;
        }

        if (empty($this->type)) {
            $this->error(self::NOT_DETECTED);
            return false;
        }

        $mimetype = $this->getMimeType(true);
        if (in_array($this->type, $mimetype)) {
            $this->error(self::FALSE_TYPE);
            return false;
        }

        $types = explode('/', $this->type);
        $types = array_merge($types, explode('-', $this->type));
        $types = array_merge($types, explode(';', $this->type));
        foreach ($mimetype as $mime) {
            if (in_array($mime, $types)) {
                $this->error(self::FALSE_TYPE);
                return false;
            }
        }

        return true;
    }
}
