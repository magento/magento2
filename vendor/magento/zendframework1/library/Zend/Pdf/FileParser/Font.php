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
 * @package    Zend_Pdf
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Internally used classes */
#require_once 'Zend/Pdf/Font.php';


/** Zend_Pdf_FileParser */
#require_once 'Zend/Pdf/FileParser.php';

/**
 * Abstract helper class for {@link Zend_Pdf_Font} that parses font files.
 *
 * Defines the public interface for concrete subclasses which are responsible
 * for parsing the raw binary data from the font file on disk. Also provides
 * a debug logging interface and a couple of shared utility methods.
 *
 * @package    Zend_Pdf
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_FileParser_Font extends Zend_Pdf_FileParser
{
  /**** Instance Variables ****/


    /**
     * Array of parsed font properties. Used with {@link __get()} and
     * {@link __set()}.
     * @var array
     */
    private $_fontProperties = array();

    /**
     * Flag indicating whether or not debug logging is active.
     * @var boolean
     */
    private $_debug = false;



  /**** Public Interface ****/


  /* Object Lifecycle */

    /**
     * Object constructor.
     *
     * Validates the data source and enables debug logging if so configured.
     *
     * @param Zend_Pdf_FileParserDataSource $dataSource
     * @throws Zend_Pdf_Exception
     */
    public function __construct(Zend_Pdf_FileParserDataSource $dataSource)
    {
        parent::__construct($dataSource);
        $this->fontType = Zend_Pdf_Font::TYPE_UNKNOWN;
    }


  /* Accessors */

    /**
     * Get handler
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        if (isset($this->_fontProperties[$property])) {
            return $this->_fontProperties[$property];
        } else {
            return null;
        }
    }

    /* NOTE: The set handler is defined below in the internal methods group. */


  /* Parser Methods */

    /**
     * Reads the Unicode UTF-16-encoded string from the binary file at the
     * current offset location. Overridden to fix return character set at UTF-16BE.
     *
     * @todo Deal with to-dos in the parent method.
     *
     * @param integer $byteCount Number of bytes (characters * 2) to return.
     * @param integer $byteOrder (optional) Big- or little-endian byte order.
     *   Use the BYTE_ORDER_ constants defined in {@link Zend_Pdf_FileParser}. If
     *   omitted, uses big-endian.
     * @param string $characterSet (optional) --Ignored--
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public function readStringUTF16($byteCount,
                                    $byteOrder = Zend_Pdf_FileParser::BYTE_ORDER_BIG_ENDIAN,
                                    $characterSet = '')
    {
        return parent::readStringUTF16($byteCount, $byteOrder, 'UTF-16BE');
    }

    /**
     * Reads the Mac Roman-encoded string from the binary file at the current
     * offset location. Overridden to fix return character set at UTF-16BE.
     *
     * @param integer $byteCount Number of bytes (characters) to return.
     * @param string $characterSet (optional) --Ignored--
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public function readStringMacRoman($byteCount, $characterSet = '')
    {
        return parent::readStringMacRoman($byteCount, 'UTF-16BE');
    }

    /**
     * Reads the Pascal string from the binary file at the current offset
     * location. Overridden to fix return character set at UTF-16BE.
     *
     * @param string $characterSet (optional) --Ignored--
     * @param integer $lengthBytes (optional) Number of bytes that make up the
     *   length. Default is 1.
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public function readStringPascal($characterSet = '', $lengthBytes = 1)
    {
        return parent::readStringPascal('UTF-16BE');
    }


  /* Utility Methods */

    /**
     * Writes the entire font properties array to STDOUT. Used only for debugging.
     */
    public function writeDebug()
    {
        print_r($this->_fontProperties);
    }



  /**** Internal Methods ****/


  /* Internal Accessors */

    /**
     * Set handler
     *
     * NOTE: This method is protected. Other classes may freely interrogate
     * the font properties, but only this and its subclasses may set them.
     *
     * @param string $property
     * @param  mixed $value
     */
    public function __set($property, $value)
    {
        if ($value === null) {
            unset($this->_fontProperties[$property]);
        } else {
            $this->_fontProperties[$property] = $value;
        }
    }


  /* Internal Utility Methods */

    /**
     * If debug logging is enabled, writes the log message.
     *
     * The log message is a sprintf() style string and any number of arguments
     * may accompany it as additional parameters.
     *
     * @param string $message
     * @param mixed (optional, multiple) Additional arguments
     */
    protected function _debugLog($message)
    {
        if (! $this->_debug) {
            return;
        }
        if (func_num_args() > 1) {
            $args = func_get_args();
            $message = array_shift($args);
            $message = vsprintf($message, $args);
        }

        #require_once 'Zend/Log.php';
        $logger = new Zend_Log();
        $logger->log($message, Zend_Log::DEBUG);
    }
}
