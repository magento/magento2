<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Validator
 */

namespace Zend\Validator\File;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\ErrorHandler;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

/**
 * Validator for the mime type of a file
 *
 * @category  Zend
 * @package   Zend_Validate
 */
class MimeType extends AbstractValidator
{
    /**#@+
     * @const Error type constants
     */
    const FALSE_TYPE   = 'fileMimeTypeFalse';
    const NOT_DETECTED = 'fileMimeTypeNotDetected';
    const NOT_READABLE = 'fileMimeTypeNotReadable';
    /**#@-*/

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::FALSE_TYPE   => "File '%value%' has a false mimetype of '%type%'",
        self::NOT_DETECTED => "The mimetype of file '%value%' could not be detected",
        self::NOT_READABLE => "File '%value%' is not readable or does not exist",
    );

    /**
     * @var array
     */
    protected $messageVariables = array(
        'type' => 'type'
    );

    /**
     * @var string
     */
    protected $type;

    /**
     * Finfo object to use
     *
     * @var resource
     */
    protected $finfo;

    /**
     * If no environment variable 'MAGIC' is set, try and autodiscover it based on common locations
     * @var array
     */
    protected $magicFiles = array(
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
     * Options for this validator
     *
     * @var array
     */
    protected $options = array(
        'enableHeaderCheck' => false,  // Allow header check
        'disableMagicFile'  => false,  // Disable usage of magicfile
        'magicFile'         => null,   // Magicfile to use
        'mimeType'          => null,   // Mimetype to allow
    );

    /**
     * Sets validator options
     *
     * Mimetype to accept
     * - NULL means default PHP usage by using the environment variable 'magic'
     * - FALSE means disabling searching for mimetype, should be used for PHP 5.3
     * - A string is the mimetype file to use
     *
     * @param  string|array|Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (is_string($options)) {
            $this->setMimeType($options);
            $options = array();
        }

        if (isset($options['magicFile'])) {
            $this->setMagicFile($options['magicFile']);
            unset($options['magicFile']);
        }

        if (isset($options['enableHeaderCheck'])) {
            $this->enableHeaderCheck($options['enableHeaderCheck']);
            unset($options['enableHeaderCheck']);
        }

        if (array_key_exists('mimeType', $options)) {
            $this->setMimeType($options['mimeType']);
            unset($options['mimeType']);
        }

        // Handle cases where mimetypes are interspersed with options, or
        // options are simply an array of mime types
        foreach (array_keys($options) as $key) {
            if (!is_int($key)) {
                continue;
            }
            $this->addMimeType($options[$key]);
            unset($options[$key]);
        }

        parent::__construct($options);
    }

    /**
     * Returns the actual set magicfile
     *
     * @return string
     */
    public function getMagicFile()
    {
        if (null === $this->options['magicFile']) {
            $magic = getenv('magic');
            if (!empty($magic)) {
                $this->setMagicFile($magic);
                if ($this->options['magicFile'] === null) {
                    $this->options['magicFile'] = false;
                }
                return $this->options['magicFile'];
            }

            ErrorHandler::start();
            $safeMode = ini_get('safe_mode');
            ErrorHandler::stop();

            if (!($safeMode == 'On' || $safeMode === 1)) {
                foreach ($this->magicFiles as $file) {
                    // suppressing errors which are thrown due to openbase_dir restrictions
                    try {
                        $this->setMagicFile($file);
                        if ($this->options['magicFile'] !== null) {
                            break;
                        }
                    } catch (Exception\ExceptionInterface $e) {
                        // Intentionally, catch and fall through
                    }
                }
            }

            if ($this->options['magicFile'] === null) {
                $this->options['magicFile'] = false;
            }
        }

        return $this->options['magicFile'];
    }

    /**
     * Sets the magicfile to use
     * if null, the MAGIC constant from php is used
     * if the MAGIC file is erroneous, no file will be set
     * if false, the default MAGIC file from PHP will be used
     *
     * @param  string $file
     * @return MimeType Provides fluid interface
     * @throws Exception\RuntimeException When finfo can not read the magicfile
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidMagicMimeFileException
     */
    public function setMagicFile($file)
    {
        if ($file === false) {
            $this->options['magicFile'] = false;
        } elseif (empty($file)) {
            $this->options['magicFile'] = null;
        } elseif (!(class_exists('finfo', false))) {
            $this->options['magicFile'] = null;
            throw new Exception\RuntimeException('Magicfile can not be set; there is no finfo extension installed');
        } elseif (!is_file($file) || !is_readable($file)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The given magicfile ("%s") could not be read',
                $file
            ));
        } else {
            $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
            ErrorHandler::start(E_NOTICE|E_WARNING);
            $this->finfo = finfo_open($const, $file);
            $error       = ErrorHandler::stop();
            if (empty($this->finfo)) {
                $this->finfo = null;
                throw new Exception\InvalidMagicMimeFileException(sprintf(
                    'The given magicfile ("%s") could not be used by ext/finfo',
                    $file
                ), 0, $error);
            }
            $this->options['magicFile'] = $file;
        }

        return $this;
    }

    /**
     * Disables usage of MagicFile
     *
     * @param $disable boolean False disables usage of magic file
     * @return MimeType Provides fluid interface
     */
    public function disableMagicFile($disable)
    {
        $this->options['disableMagicFile'] = (bool) $disable;
        return $this;
    }

    /**
     * Is usage of MagicFile disabled?
     *
     * @return boolean
     */
    public function isMagicFileDisabled()
    {
        return $this->options['disableMagicFile'];
    }

    /**
     * Returns the Header Check option
     *
     * @return boolean
     */
    public function getHeaderCheck()
    {
        return $this->options['enableHeaderCheck'];
    }

    /**
     * Defines if the http header should be used
     * Note that this is unsafe and therefor the default value is false
     *
     * @param  boolean $headerCheck
     * @return MimeType Provides fluid interface
     */
    public function enableHeaderCheck($headerCheck = true)
    {
        $this->options['enableHeaderCheck'] = (boolean) $headerCheck;
        return $this;
    }

    /**
     * Returns the set mimetypes
     *
     * @param  boolean $asArray Returns the values as array, when false a concatenated string is returned
     * @return string|array
     */
    public function getMimeType($asArray = false)
    {
        $asArray  = (bool) $asArray;
        $mimetype = (string) $this->options['mimeType'];
        if ($asArray) {
            $mimetype = explode(',', $mimetype);
        }

        return $mimetype;
    }

    /**
     * Sets the mimetypes
     *
     * @param  string|array $mimetype The mimetypes to validate
     * @return MimeType Provides a fluent interface
     */
    public function setMimeType($mimetype)
    {
        $this->options['mimeType'] = null;
        $this->addMimeType($mimetype);
        return $this;
    }

    /**
     * Adds the mimetypes
     *
     * @param  string|array $mimetype The mimetypes to add for validation
     * @return MimeType Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function addMimeType($mimetype)
    {
        $mimetypes = $this->getMimeType(true);

        if (is_string($mimetype)) {
            $mimetype = explode(',', $mimetype);
        } elseif (!is_array($mimetype)) {
            throw new Exception\InvalidArgumentException("Invalid options to validator provided");
        }

        if (isset($mimetype['magicFile'])) {
            unset($mimetype['magicFile']);
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

        $this->options['mimeType'] = implode(',', $mimetypes);

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
     * @param  array  $file  File data from \Zend\File\Transfer\Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        if ($file === null) {
            $file = array(
                'type' => null,
                'name' => $value,
            );
        }

        // Is file readable ?
        if (false === stream_resolve_include_path($value)) {
            return $this->createError($file, self::NOT_READABLE);
        }

        $mimefile = $this->getMagicFile();
        if (class_exists('finfo', false)) {
            $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
            if (!$this->isMagicFileDisabled() && (!empty($mimefile) && empty($this->finfo))) {
                ErrorHandler::start(E_NOTICE|E_WARNING);
                $this->finfo = finfo_open($const, $mimefile);
                ErrorHandler::stop();
            }

            if (empty($this->finfo)) {
                ErrorHandler::start(E_NOTICE|E_WARNING);
                $this->finfo = finfo_open($const);
                ErrorHandler::stop();
            }

            $this->type = null;
            if (!empty($this->finfo)) {
                $this->type = finfo_file($this->finfo, $value);
            }
        }

        if (empty($this->type) &&
            (function_exists('mime_content_type') && ini_get('mime_magic.magicfile'))) {
                $this->type = mime_content_type($value);
        }

        if (empty($this->type) && $this->getHeaderCheck()) {
            $this->type = $file['type'];
        }

        if (empty($this->type)) {
            return $this->createError($file, self::NOT_DETECTED);
        }

        $mimetype = $this->getMimeType(true);
        if (in_array($this->type, $mimetype)) {
            return true;
        }

        $types = explode('/', $this->type);
        $types = array_merge($types, explode('-', $this->type));
        $types = array_merge($types, explode(';', $this->type));
        foreach ($mimetype as $mime) {
            if (in_array($mime, $types)) {
                return true;
            }
        }

        return $this->createError($file, self::FALSE_TYPE);
    }

    /**
     * Throws an error of the given type
     *
     * @param  string $file
     * @param  string $errorType
     * @return false
     */
    protected function createError($file, $errorType)
    {
        if ($file !== null) {
            if (is_array($file)) {
                if (array_key_exists('name', $file)) {
                    $file = $file['name'];
                }
            }

            if (is_string($file)) {
                $this->value = basename($file);
            }
        }

        $this->error($errorType);
        return false;
    }
}
