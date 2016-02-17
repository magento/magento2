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
 * @category   Zend
 * @package    Zend_Mime
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Mime
 */
#require_once 'Zend/Mime.php';

/**
 * Class representing a MIME part.
 *
 * @category   Zend
 * @package    Zend_Mime
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mime_Part
{

    /**
     * Type
     *
     * @var string
     */
    public $type = Zend_Mime::TYPE_OCTETSTREAM;

    /**
     * Encoding
     *
     * @var string
     */
    public $encoding = Zend_Mime::ENCODING_8BIT;

    /**
     * ID
     *
     * @var string
     */
    public $id;

    /**
     * Disposition
     *
     * @var string
     */
    public $disposition;

    /**
     * Filename
     *
     * @var string
     */
    public $filename;

    /**
     * Description
     *
     * @var string
     */
    public $description;

    /**
     * Character set
     *
     * @var string
     */
    public $charset;

    /**
     * Boundary
     *
     * @var string
     */
    public $boundary;

    /**
     * Location
     *
     * @var string
     */
    public $location;

    /**
     * Language
     *
     * @var string
     */
    public $language;

    /**
     * Content
     *
     * @var mixed
     */
    protected $_content;

    /**
     * @var bool
     */
    protected $_isStream = false;

    /**
     * create a new Mime Part.
     * The (unencoded) content of the Part as passed
     * as a string or stream
     *
     * @param mixed $content String or Stream containing the content
     */
    public function __construct($content)
    {
        $this->_content = $content;
        if (is_resource($content)) {
            $this->_isStream = true;
        }
    }

    /**
     * @todo setters/getters
     * @todo error checking for setting $type
     * @todo error checking for setting $encoding
     */

    /**
     * check if this part can be read as a stream.
     * if true, getEncodedStream can be called, otherwise
     * only getContent can be used to fetch the encoded
     * content of the part
     *
     * @return bool
     */
    public function isStream()
    {
        return $this->_isStream;
    }

    /**
     * if this was created with a stream, return a filtered stream for
     * reading the content. very useful for large file attachments.
     *
     * @return mixed Stream
     * @throws Zend_Mime_Exception if not a stream or unable to append filter
     */
    public function getEncodedStream()
    {
        if (!$this->_isStream) {
            #require_once 'Zend/Mime/Exception.php';
            throw new Zend_Mime_Exception(
                'Attempt to get a stream from a string part'
            );
        }

        //stream_filter_remove(); // ??? is that right?
        switch ($this->encoding) {
            case Zend_Mime::ENCODING_QUOTEDPRINTABLE:
                $filter = stream_filter_append(
                    $this->_content,
                    'convert.quoted-printable-encode',
                    STREAM_FILTER_READ,
                    array(
                        'line-length'      => 76,
                        'line-break-chars' => Zend_Mime::LINEEND
                    )
                );
                if (!is_resource($filter)) {
                    #require_once 'Zend/Mime/Exception.php';
                    throw new Zend_Mime_Exception(
                        'Failed to append quoted-printable filter'
                    );
                }
                break;

            case Zend_Mime::ENCODING_BASE64:
                $filter = stream_filter_append(
                    $this->_content,
                    'convert.base64-encode',
                    STREAM_FILTER_READ,
                    array(
                        'line-length'      => 76,
                        'line-break-chars' => Zend_Mime::LINEEND
                    )
                );
                if (!is_resource($filter)) {
                    #require_once 'Zend/Mime/Exception.php';
                    throw new Zend_Mime_Exception(
                        'Failed to append base64 filter'
                    );
                }
                break;

            default:
        }

        return $this->_content;
    }

    /**
     * Get the Content of the current Mime Part in the given encoding.
     *
     * @param  string $EOL Line end; defaults to {@link Zend_Mime::LINEEND}
     * @throws Zend_Mime_Exception
     * @return string
     */
    public function getContent($EOL = Zend_Mime::LINEEND)
    {
        if ($this->_isStream) {
            return stream_get_contents($this->getEncodedStream());
        } else {
            return Zend_Mime::encode($this->_content, $this->encoding, $EOL);
        }
    }

    /**
     * Get the RAW unencoded content from this part
     *
     * @return string
     */
    public function getRawContent()
    {
        if ($this->_isStream) {
            return stream_get_contents($this->_content);
        } else {
            return $this->_content;
        }
    }

    /**
     * Create and return the array of headers for this MIME part
     *
     * @param  string $EOL Line end; defaults to {@link Zend_Mime::LINEEND}
     * @return array
     */
    public function getHeadersArray($EOL = Zend_Mime::LINEEND)
    {
        $headers = array();

        $contentType = $this->type;
        if ($this->charset) {
            $contentType .= '; charset=' . $this->charset;
        }

        if ($this->boundary) {
            $contentType .= ';' . $EOL
                            . " boundary=\"" . $this->boundary . '"';
        }

        $headers[] = array(
            'Content-Type',
            $contentType
        );

        if ($this->encoding) {
            $headers[] = array(
                'Content-Transfer-Encoding',
                $this->encoding
            );
        }

        if ($this->id) {
            $headers[] = array(
                'Content-ID',
                '<' . $this->id . '>'
            );
        }

        if ($this->disposition) {
            $disposition = $this->disposition;
            if ($this->filename) {
                $disposition .= '; filename="' . $this->filename . '"';
            }
            $headers[] = array(
                'Content-Disposition',
                $disposition
            );
        }

        if ($this->description) {
            $headers[] = array(
                'Content-Description',
                $this->description
            );
        }

        if ($this->location) {
            $headers[] = array(
                'Content-Location',
                $this->location
            );
        }

        if ($this->language) {
            $headers[] = array(
                'Content-Language',
                $this->language
            );
        }

        return $headers;
    }

    /**
     * Return the headers for this part as a string
     *
     * @param  string $EOL Line end; defaults to {@link Zend_Mime::LINEEND}
     * @return string
     */
    public function getHeaders($EOL = Zend_Mime::LINEEND)
    {
        $res = '';
        foreach ($this->getHeadersArray($EOL) as $header) {
            $res .= $header[0] . ': ' . $header[1] . $EOL;
        }

        return $res;
    }
}
