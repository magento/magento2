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
 * @package    Zend_Amf
 * @subpackage Util
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: BinaryStream.php 22101 2010-05-04 20:07:13Z matthew $
 */

/**
 * Utility class to walk through a data stream byte by byte with conventional names
 *
 * @package    Zend_Amf
 * @subpackage Util
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Util_BinaryStream
{
    /**
     * @var string Byte stream
     */
    protected $_stream;

    /**
     * @var int Length of stream
     */
    protected $_streamLength;

    /**
     * @var bool BigEndian encoding?
     */
    protected $_bigEndian;

    /**
     * @var int Current position in stream
     */
    protected $_needle;

    /**
     * Constructor
     *
     * Create a reference to a byte stream that is going to be parsed or created
     * by the methods in the class. Detect if the class should use big or
     * little Endian encoding.
     *
     * @param  string $stream use '' if creating a new stream or pass a string if reading.
     * @return void
     */
    public function __construct($stream)
    {
        if (!is_string($stream)) {
            #require_once 'Zend/Amf/Exception.php';
            throw new Zend_Amf_Exception('Inputdata is not of type String');
        }

        $this->_stream       = $stream;
        $this->_needle       = 0;
        $this->_streamLength = strlen($stream);
        $this->_bigEndian    = (pack('l', 1) === "\x00\x00\x00\x01");
    }

    /**
     * Returns the current stream
     *
     * @return string
     */
    public function getStream()
    {
        return $this->_stream;
    }

    /**
     * Read the number of bytes in a row for the length supplied.
     *
     * @todo   Should check that there are enough bytes left in the stream we are about to read.
     * @param  int $length
     * @return string
     * @throws Zend_Amf_Exception for buffer underrun
     */
    public function readBytes($length)
    {
        if (($length + $this->_needle) > $this->_streamLength) {
            #require_once 'Zend/Amf/Exception.php';
            throw new Zend_Amf_Exception('Buffer underrun at needle position: ' . $this->_needle . ' while requesting length: ' . $length);
        }
        $bytes = substr($this->_stream, $this->_needle, $length);
        $this->_needle+= $length;
        return $bytes;
    }

    /**
     * Write any length of bytes to the stream
     *
     * Usually a string.
     *
     * @param  string $bytes
     * @return Zend_Amf_Util_BinaryStream
     */
    public function writeBytes($bytes)
    {
        $this->_stream.= $bytes;
        return $this;
    }

    /**
     * Reads a signed byte
     *
     * @return int Value is in the range of -128 to 127.
     */
    public function readByte()
    {
        if (($this->_needle + 1) > $this->_streamLength) {
            #require_once 'Zend/Amf/Exception.php';
            throw new Zend_Amf_Exception('Buffer underrun at needle position: ' . $this->_needle . ' while requesting length: ' . $length);
        }

        return ord($this->_stream{$this->_needle++});
    }

    /**
     * Writes the passed string into a signed byte on the stream.
     *
     * @param  string $stream
     * @return Zend_Amf_Util_BinaryStream
     */
    public function writeByte($stream)
    {
        $this->_stream.= pack('c', $stream);
        return $this;
    }

    /**
     * Reads a signed 32-bit integer from the data stream.
     *
     * @return int Value is in the range of -2147483648 to 2147483647
     */
    public function readInt()
    {
        return ($this->readByte() << 8) + $this->readByte();
    }

    /**
     * Write an the integer to the output stream as a 32 bit signed integer
     *
     * @param  int $stream
     * @return Zend_Amf_Util_BinaryStream
     */
    public function writeInt($stream)
    {
        $this->_stream.= pack('n', $stream);
        return $this;
    }

    /**
     * Reads a UTF-8 string from the data stream
     *
     * @return string A UTF-8 string produced by the byte representation of characters
     */
    public function readUtf()
    {
        $length = $this->readInt();
        return $this->readBytes($length);
    }

    /**
     * Wite a UTF-8 string to the outputstream
     *
     * @param  string $stream
     * @return Zend_Amf_Util_BinaryStream
     */
    public function writeUtf($stream)
    {
        $this->writeInt(strlen($stream));
        $this->_stream.= $stream;
        return $this;
    }


    /**
     * Read a long UTF string
     *
     * @return string
     */
    public function readLongUtf()
    {
        $length = $this->readLong();
        return $this->readBytes($length);
    }

    /**
     * Write a long UTF string to the buffer
     *
     * @param  string $stream
     * @return Zend_Amf_Util_BinaryStream
     */
    public function writeLongUtf($stream)
    {
        $this->writeLong(strlen($stream));
        $this->_stream.= $stream;
    }

    /**
     * Read a long numeric value
     *
     * @return double
     */
    public function readLong()
    {
        return ($this->readByte() << 24) + ($this->readByte() << 16) + ($this->readByte() << 8) + $this->readByte();
    }

    /**
     * Write long numeric value to output stream
     *
     * @param  int|string $stream
     * @return Zend_Amf_Util_BinaryStream
     */
    public function writeLong($stream)
    {
        $this->_stream.= pack('N', $stream);
        return $this;
    }

    /**
     * Read a 16 bit unsigned short.
     *
     * @todo   This could use the unpack() w/ S,n, or v
     * @return double
     */
    public function readUnsignedShort()
    {
        $byte1 = $this->readByte();
        $byte2 = $this->readByte();
        return (($byte1 << 8) | $byte2);
    }

    /**
     * Reads an IEEE 754 double-precision floating point number from the data stream.
     *
     * @return double Floating point number
     */
    public function readDouble()
    {
        $bytes = substr($this->_stream, $this->_needle, 8);
        $this->_needle+= 8;

        if (!$this->_bigEndian) {
            $bytes = strrev($bytes);
        }

        $double = unpack('dflt', $bytes);
        return $double['flt'];
    }

    /**
     * Writes an IEEE 754 double-precision floating point number from the data stream.
     *
     * @param  string|double $stream
     * @return Zend_Amf_Util_BinaryStream
     */
    public function writeDouble($stream)
    {
        $stream = pack('d', $stream);
        if (!$this->_bigEndian) {
            $stream = strrev($stream);
        }
        $this->_stream.= $stream;
        return $this;
    }

}
