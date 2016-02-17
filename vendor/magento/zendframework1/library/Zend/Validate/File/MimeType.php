<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id$
 */

/**
 * @see Zend_Validate_Abstract
 */
#require_once 'Zend/Validate/Abstract.php';

/**
 * Validator for the mime type of a file
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_MimeType extends Zend_Validate_Abstract
{
    /**
     * @const Error type constants
     */
    const FALSE_TYPE   = 'fileMimeTypeFalse';
    const NOT_DETECTED = 'fileMimeTypeNotDetected';
    const NOT_READABLE = 'fileMimeTypeNotReadable';

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::FALSE_TYPE   => "File '%value%' has a false mimetype of '%type%'",
        self::NOT_DETECTED => "The mimetype of file '%value%' could not be detected",
        self::NOT_READABLE => "File '%value%' is not readable or does not exist",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'type' => '_type'
    );

    /**
     * @var string
     */
    protected $_type;

    /**
     * Mimetypes
     *
     * If null, there is no mimetype
     *
     * @var string|null
     */
    protected $_mimetype;

    /**
     * Magicfile to use
     *
     * @var string|null
     */
    protected $_magicfile;

    /**
     * Finfo object to use
     *
     * @var resource
     */
    protected $_finfo;

    /**
     * If no $_ENV['MAGIC'] is set, try and autodiscover it based on common locations
     * @var array
     */
    protected $_magicFiles = array(
        '/usr/share/misc/magic',
        '/usr/share/misc/magic.mime',
        '/usr/share/misc/magic.mgc',
        '/usr/share/mime/magic',
        '/usr/share/mime/magic.mime',
        '/usr/share/mime/magic.mgc',
        '/usr/share/file/magic',
        '/usr/share/file/magic.mime',
        '/usr/share/file/magic.mgc',
    );

    /**
     * Indicates whether use of $_magicFiles should be attempted.
     * @var boolean
     */
    protected $_tryCommonMagicFiles = true;

    /**
     * Option to allow header check
     *
     * @var boolean
     */
    protected $_headerCheck = false;

    /**
     * Holds error information returned by finfo_open
     *
     * @var array
     */
    protected $_finfoError;

    /**
     * Sets validator options
     *
     * Mimetype to accept
     *
     * @param  string|array $mimetype MimeType
     * @throws Zend_Validate_Exception
     */
    public function __construct($mimetype)
    {
        if ($mimetype instanceof Zend_Config) {
            $mimetype = $mimetype->toArray();
        } elseif (is_string($mimetype)) {
            $mimetype = explode(',', $mimetype);
        } elseif (!is_array($mimetype)) {
            #require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Invalid options to validator provided");
        }

        if (isset($mimetype['magicfile'])) {
            $this->setMagicFile($mimetype['magicfile']);
            unset($mimetype['magicfile']);
        }

        if (isset($mimetype['headerCheck'])) {
            $this->enableHeaderCheck($mimetype['headerCheck']);
            unset($mimetype['headerCheck']);
        }

        $this->setMimeType($mimetype);
    }

    /**
     * Returns the actual set magicfile
     *
     * Note that for PHP 5.3.0 or higher, we don't use $_ENV['MAGIC'] or try to
     * find a magic file in a common location as PHP now has a built-in internal
     * magic file.
     *
     * @return string
     */
    public function getMagicFile()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')
            && null === $this->_magicfile) {
            if (!empty($_ENV['MAGIC'])) {
                $this->setMagicFile($_ENV['MAGIC']);
            } elseif (
                !(@ini_get("safe_mode") == 'On' || @ini_get("safe_mode") === 1)
                && $this->shouldTryCommonMagicFiles() // @see ZF-11784
            ) {
                #require_once 'Zend/Validate/Exception.php';
                foreach ($this->_magicFiles as $file) {
                    // supressing errors which are thrown due to openbase_dir restrictions
                    try {
                        $this->setMagicFile($file);
                        if ($this->_magicfile !== null) {
                            break;
                        }
                    } catch (Zend_Validate_Exception $e) {
                        // Intentionally, catch and fall through
                    }
                }
            }

            if ($this->_magicfile === null) {
                $this->_magicfile = false;
            }
        }

        return $this->_magicfile;
    }

    /**
     * Sets the magicfile to use
     * if null, the MAGIC constant from php is used
     * if the MAGIC file is errorous, no file will be set
     *
     * @param  string $file
     * @throws Zend_Validate_Exception When finfo can not read the magicfile
     * @return Zend_Validate_File_MimeType Provides a fluent interface
     */
    public function setMagicFile($file)
    {
        if (empty($file)) {
            $this->_magicfile = null;
        } else if (!(class_exists('finfo', false))) {
            $this->_magicfile = null;
            #require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Magicfile can not be set. There is no finfo extension installed');
        } else if (!is_file($file) || !is_readable($file)) {
            #require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('The given magicfile can not be read');
        } else {
            $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
            set_error_handler(array($this, '_errorHandler'), E_NOTICE | E_WARNING);
            $this->_finfo = finfo_open($const, $file);
            restore_error_handler();
            if (empty($this->_finfo)) {
                $this->_finfo = null;
                #require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception(
                    sprintf('The given magicfile ("%s") is not accepted by finfo', $file),
                    null,
                    $this->_finfoError
                );
            } else {
                $this->_magicfile = $file;
            }
        }

        return $this;
    }

    /**
     * Enables or disables attempts to try the common magic file locations
     * specified by Zend_Validate_File_MimeType::_magicFiles
     *
     * @param  boolean $flag
     * @return Zend_Validate_File_MimeType Provides fluent interface
     * @see http://framework.zend.com/issues/browse/ZF-11784
     */
    public function setTryCommonMagicFilesFlag($flag = true)
    {
        $this->_tryCommonMagicFiles = (boolean) $flag;

        return $this;
    }

    /**
     * Accessor for Zend_Validate_File_MimeType::_magicFiles
     *
     * @return boolean
     * @see http://framework.zend.com/issues/browse/ZF-11784
     */
    public function shouldTryCommonMagicFiles()
    {
        return $this->_tryCommonMagicFiles;
    }

    /**
     * Returns the Header Check option
     *
     * @return boolean
     */
    public function getHeaderCheck()
    {
        return $this->_headerCheck;
    }

    /**
     * Defines if the http header should be used
     * Note that this is unsave and therefor the default value is false
     *
     * @param  boolean $headerCheck
     * @return Zend_Validate_File_MimeType Provides a fluent interface
     */
    public function enableHeaderCheck($headerCheck = true)
    {
        $this->_headerCheck = (boolean) $headerCheck;
        return $this;
    }

    /**
     * Returns the set mimetypes
     *
     * @param  boolean $asArray Returns the values as array, when false an concated string is returned
     * @return string|array
     */
    public function getMimeType($asArray = false)
    {
        $asArray   = (bool) $asArray;
        $mimetype = (string) $this->_mimetype;
        if ($asArray) {
            $mimetype = explode(',', $mimetype);
        }

        return $mimetype;
    }

    /**
     * Sets the mimetypes
     *
     * @param  string|array $mimetype The mimetypes to validate
     * @return Zend_Validate_File_Extension Provides a fluent interface
     */
    public function setMimeType($mimetype)
    {
        $this->_mimetype = null;
        $this->addMimeType($mimetype);
        return $this;
    }

    /**
     * Adds the mimetypes
     *
     * @param  string|array $mimetype The mimetypes to add for validation
     * @throws Zend_Validate_Exception
     * @return Zend_Validate_File_Extension Provides a fluent interface
     */
    public function addMimeType($mimetype)
    {
        $mimetypes = $this->getMimeType(true);

        if (is_string($mimetype)) {
            $mimetype = explode(',', $mimetype);
        } elseif (!is_array($mimetype)) {
            #require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Invalid options to validator provided");
        }

        if (isset($mimetype['magicfile'])) {
            unset($mimetype['magicfile']);
        }

        foreach ($mimetype as $content) {
            if (empty($content) || !is_string($content)) {
                continue;
            }
            $mimetypes[] = trim($content);
        }
        $mimetypes = array_unique($mimetypes);

        // Sanity check to ensure no empty values
        foreach ($mimetypes as $key => $mt) {
            if (empty($mt)) {
                unset($mimetypes[$key]);
            }
        }

        $this->_mimetype = implode(',', $mimetypes);

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if the mimetype of the file matches the given ones. Also parts
     * of mimetypes can be checked. If you give for example "image" all image
     * mime types will be accepted like "image/gif", "image/jpeg" and so on.
     *
     * @param  string $value Real file to check for mimetype
     * @param  array  $file  File data from Zend_File_Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        if ($file === null) {
            $file = array(
                'type' => null,
                'name' => $value
            );
        }

        // Is file readable ?
        #require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_READABLE);
        }

        $this->_type = $this->_detectMimeType($value);

        if (empty($this->_type) && $this->_headerCheck) {
            $this->_type = $file['type'];
        }

        if (empty($this->_type)) {
            return $this->_throw($file, self::NOT_DETECTED);
        }

        $mimetype = $this->getMimeType(true);
        if (in_array($this->_type, $mimetype)) {
            return true;
        }

        $types = explode('/', $this->_type);
        $types = array_merge($types, explode('-', $this->_type));
        $types = array_merge($types, explode(';', $this->_type));
        foreach($mimetype as $mime) {
            if (in_array($mime, $types)) {
                return true;
            }
        }

        return $this->_throw($file, self::FALSE_TYPE);
    }

    /**
     * Throws an error of the given type
     *
     * @param  string $file
     * @param  string $errorType
     * @return false
     */
    protected function _throw($file, $errorType)
    {
        $this->_value = $file['name'];
        $this->_error($errorType);
        return false;
    }

    /**
     * Try to detect mime type of given file.
     * @param string $file File which mime type should be detected
     * @return string File mime type or null if not detected
     */
    protected function _detectMimeType($file)
    {
        $mimefile = $this->getMagicFile();
        $type = null;

        if (class_exists('finfo', false)) {
            $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;

            if (!empty($mimefile) && empty($this->_finfo)) {
                set_error_handler(array($this, '_errorHandler'), E_NOTICE | E_WARNING);
                $this->_finfo = finfo_open($const, $mimefile);
                restore_error_handler();
            }

            if (empty($this->_finfo)) {
                set_error_handler(array($this, '_errorHandler'), E_NOTICE | E_WARNING);
                $this->_finfo = finfo_open($const);
                restore_error_handler();
            }

            if (!empty($this->_finfo)) {
                $type = finfo_file($this->_finfo, $file);
            }
        }

        if (empty($type) &&
            (function_exists('mime_content_type') && ini_get('mime_magic.magicfile'))) {
                $type = mime_content_type($file);
        }

        return $type;
    }

    /**
     * Saves the provided error information by finfo_open to this instance
     *
     * @param integer $errno
     * @param string  $errstr
     * @param string  $errfile
     * @param integer $errline
     */
    protected function _errorHandler($errno, $errstr, $errfile, $errline)
    {
        $this->_finfoError = new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}
