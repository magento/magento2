<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\File;

use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * Validator which checks if the file already exists in the directory
 */
class IsCompressed extends MimeType
{
    /**
     * @const string Error constants
     */
    const FALSE_TYPE   = 'fileIsCompressedFalseType';
    const NOT_DETECTED = 'fileIsCompressedNotDetected';
    const NOT_READABLE = 'fileIsCompressedNotReadable';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::FALSE_TYPE   => "File is not compressed, '%type%' detected",
        self::NOT_DETECTED => "The mimetype could not be detected from the file",
        self::NOT_READABLE => "File is not readable or does not exist",
    );

    /**
     * Sets validator options
     *
     * @param string|array|Traversable $options
     */
    public function __construct($options = array())
    {
        // http://hul.harvard.edu/ois/systems/wax/wax-public-help/mimetypes.htm
        $default = array(
            'application/arj',
            'application/gnutar',
            'application/lha',
            'application/lzx',
            'application/vnd.ms-cab-compressed',
            'application/x-ace-compressed',
            'application/x-arc',
            'application/x-archive',
            'application/x-arj',
            'application/x-bzip',
            'application/x-bzip2',
            'application/x-cab-compressed',
            'application/x-compress',
            'application/x-compressed',
            'application/x-cpio',
            'application/x-debian-package',
            'application/x-eet',
            'application/x-gzip',
            'application/x-java-pack200',
            'application/x-lha',
            'application/x-lharc',
            'application/x-lzh',
            'application/x-lzma',
            'application/x-lzx',
            'application/x-rar',
            'application/x-sit',
            'application/x-stuffit',
            'application/x-tar',
            'application/zip',
            'application/x-zip',
            'application/zoo',
            'multipart/x-gzip',
        );

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if ($options === null) {
            $options = array();
        }

        parent::__construct($options);

        if (!$this->getMimeType()) {
            $this->setMimeType($default);
        }
    }
}
