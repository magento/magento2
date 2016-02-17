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
 * @package    Zend_Search_Lucene
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Search_Lucene_Storage_File */
#require_once 'Zend/Search/Lucene/Storage/File.php';

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Storage_File_Memory extends Zend_Search_Lucene_Storage_File
{
    /**
     * FileData
     *
     * @var string
     */
    private $_data;

    /**
     * File Position
     *
     * @var integer
     */
    private $_position = 0;


    /**
     * Object constractor
     *
     * @param string $data
     */
    public function __construct($data)
    {
        $this->_data = $data;
    }

    /**
     * Reads $length number of bytes at the current position in the
     * file and advances the file pointer.
     *
     * @param integer $length
     * @return string
     */
    protected function _fread($length = 1)
    {
        $returnValue = substr($this->_data, $this->_position, $length);
        $this->_position += $length;
        return $returnValue;
    }


    /**
     * Sets the file position indicator and advances the file pointer.
     * The new position, measured in bytes from the beginning of the file,
     * is obtained by adding offset to the position specified by whence,
     * whose values are defined as follows:
     * SEEK_SET - Set position equal to offset bytes.
     * SEEK_CUR - Set position to current location plus offset.
     * SEEK_END - Set position to end-of-file plus offset. (To move to
     * a position before the end-of-file, you need to pass a negative value
     * in offset.)
     * Upon success, returns 0; otherwise, returns -1
     *
     * @param integer $offset
     * @param integer $whence
     * @return integer
     */
    public function seek($offset, $whence=SEEK_SET)
    {
        switch ($whence) {
            case SEEK_SET:
                $this->_position = $offset;
                break;

            case SEEK_CUR:
                $this->_position += $offset;
                break;

            case SEEK_END:
                $this->_position = strlen($this->_data);
                $this->_position += $offset;
                break;

            default:
                break;
        }
    }

    /**
     * Get file position.
     *
     * @return integer
     */
    public function tell()
    {
        return $this->_position;
    }

    /**
     * Flush output.
     *
     * Returns true on success or false on failure.
     *
     * @return boolean
     */
    public function flush()
    {
        // Do nothing

        return true;
    }

    /**
     * Writes $length number of bytes (all, if $length===null) to the end
     * of the file.
     *
     * @param string $data
     * @param integer $length
     */
    protected function _fwrite($data, $length=null)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        if ($length !== null) {
            $this->_data .= substr($data, 0, $length);
        } else {
            $this->_data .= $data;
        }

        $this->_position = strlen($this->_data);
    }

    /**
     * Lock file
     *
     * Lock type may be a LOCK_SH (shared lock) or a LOCK_EX (exclusive lock)
     *
     * @param integer $lockType
     * @return boolean
     */
    public function lock($lockType, $nonBlockinLock = false)
    {
        // Memory files can't be shared
        // do nothing

        return true;
    }

    /**
     * Unlock file
     */
    public function unlock()
    {
        // Memory files can't be shared
        // do nothing
    }

    /**
     * Reads a byte from the current position in the file
     * and advances the file pointer.
     *
     * @return integer
     */
    public function readByte()
    {
        return ord($this->_data[$this->_position++]);
    }

    /**
     * Writes a byte to the end of the file.
     *
     * @param integer $byte
     */
    public function writeByte($byte)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        $this->_data .= chr($byte);
        $this->_position = strlen($this->_data);

        return 1;
    }

    /**
     * Read num bytes from the current position in the file
     * and advances the file pointer.
     *
     * @param integer $num
     * @return string
     */
    public function readBytes($num)
    {
        $returnValue = substr($this->_data, $this->_position, $num);
        $this->_position += $num;

        return $returnValue;
    }

    /**
     * Writes num bytes of data (all, if $num===null) to the end
     * of the string.
     *
     * @param string $data
     * @param integer $num
     */
    public function writeBytes($data, $num=null)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        if ($num !== null) {
            $this->_data .= substr($data, 0, $num);
        } else {
            $this->_data .= $data;
        }

        $this->_position = strlen($this->_data);
    }


    /**
     * Reads an integer from the current position in the file
     * and advances the file pointer.
     *
     * @return integer
     */
    public function readInt()
    {
        $str = substr($this->_data, $this->_position, 4);
        $this->_position += 4;

        return  ord($str[0]) << 24 |
                ord($str[1]) << 16 |
                ord($str[2]) << 8  |
                ord($str[3]);
    }


    /**
     * Writes an integer to the end of file.
     *
     * @param integer $value
     */
    public function writeInt($value)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        settype($value, 'integer');
        $this->_data .= chr($value>>24 & 0xFF) .
                        chr($value>>16 & 0xFF) .
                        chr($value>>8  & 0xFF) .
                        chr($value     & 0xFF);

        $this->_position = strlen($this->_data);
    }


    /**
     * Returns a long integer from the current position in the file
     * and advances the file pointer.
     *
     * @return integer
     * @throws Zend_Search_Lucene_Exception
     */
    public function readLong()
    {
        /**
         * Check, that we work in 64-bit mode.
         * fseek() uses long for offset. Thus, largest index segment file size in 32bit mode is 2Gb
         */
        if (PHP_INT_SIZE > 4) {
            $str = substr($this->_data, $this->_position, 8);
            $this->_position += 8;

            return  ord($str[0]) << 56  |
                    ord($str[1]) << 48  |
                    ord($str[2]) << 40  |
                    ord($str[3]) << 32  |
                    ord($str[4]) << 24  |
                    ord($str[5]) << 16  |
                    ord($str[6]) << 8   |
                    ord($str[7]);
        } else {
            return $this->readLong32Bit();
        }
    }

    /**
     * Writes long integer to the end of file
     *
     * @param integer $value
     * @throws Zend_Search_Lucene_Exception
     */
    public function writeLong($value)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        /**
         * Check, that we work in 64-bit mode.
         * fseek() and ftell() use long for offset. Thus, largest index segment file size in 32bit mode is 2Gb
         */
        if (PHP_INT_SIZE > 4) {
            settype($value, 'integer');
            $this->_data .= chr($value>>56 & 0xFF) .
                            chr($value>>48 & 0xFF) .
                            chr($value>>40 & 0xFF) .
                            chr($value>>32 & 0xFF) .
                            chr($value>>24 & 0xFF) .
                            chr($value>>16 & 0xFF) .
                            chr($value>>8  & 0xFF) .
                            chr($value     & 0xFF);
        } else {
            $this->writeLong32Bit($value);
        }

        $this->_position = strlen($this->_data);
    }


    /**
     * Returns a long integer from the current position in the file,
     * advances the file pointer and return it as float (for 32-bit platforms).
     *
     * @return integer|float
     * @throws Zend_Search_Lucene_Exception
     */
    public function readLong32Bit()
    {
        $wordHigh = $this->readInt();
        $wordLow  = $this->readInt();

        if ($wordHigh & (int)0x80000000) {
            // It's a negative value since the highest bit is set
            if ($wordHigh == (int)0xFFFFFFFF  &&  ($wordLow & (int)0x80000000)) {
                return $wordLow;
            } else {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Long integers lower than -2147483648 (0x80000000) are not supported on 32-bit platforms.');
            }

        }

        if ($wordLow < 0) {
            // Value is large than 0x7FFF FFFF. Represent low word as float.
            $wordLow &= 0x7FFFFFFF;
            $wordLow += (float)0x80000000;
        }

        if ($wordHigh == 0) {
            // Return value as integer if possible
            return $wordLow;
        }

        return $wordHigh*(float)0x100000000/* 0x00000001 00000000 */ + $wordLow;
    }


    /**
     * Writes long integer to the end of file (32-bit platforms implementation)
     *
     * @param integer|float $value
     * @throws Zend_Search_Lucene_Exception
     */
    public function writeLong32Bit($value)
    {
        if ($value < (int)0x80000000) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Long integers lower than -2147483648 (0x80000000) are not supported on 32-bit platforms.');
        }

        if ($value < 0) {
            $wordHigh = (int)0xFFFFFFFF;
            $wordLow  = (int)$value;
        } else {
            $wordHigh = (int)($value/(float)0x100000000/* 0x00000001 00000000 */);
            $wordLow  = $value - $wordHigh*(float)0x100000000/* 0x00000001 00000000 */;

            if ($wordLow > 0x7FFFFFFF) {
                // Highest bit of low word is set. Translate it to the corresponding negative integer value
                $wordLow -= 0x80000000;
                $wordLow |= 0x80000000;
            }
        }

        $this->writeInt($wordHigh);
        $this->writeInt($wordLow);
    }

    /**
     * Returns a variable-length integer from the current
     * position in the file and advances the file pointer.
     *
     * @return integer
     */
    public function readVInt()
    {
        $nextByte = ord($this->_data[$this->_position++]);
        $val = $nextByte & 0x7F;

        for ($shift=7; ($nextByte & 0x80) != 0; $shift += 7) {
            $nextByte = ord($this->_data[$this->_position++]);
            $val |= ($nextByte & 0x7F) << $shift;
        }
        return $val;
    }

    /**
     * Writes a variable-length integer to the end of file.
     *
     * @param integer $value
     */
    public function writeVInt($value)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        settype($value, 'integer');
        while ($value > 0x7F) {
            $this->_data .= chr( ($value & 0x7F)|0x80 );
            $value >>= 7;
        }
        $this->_data .= chr($value);

        $this->_position = strlen($this->_data);
    }


    /**
     * Reads a string from the current position in the file
     * and advances the file pointer.
     *
     * @return string
     */
    public function readString()
    {
        $strlen = $this->readVInt();
        if ($strlen == 0) {
            return '';
        } else {
            /**
             * This implementation supports only Basic Multilingual Plane
             * (BMP) characters (from 0x0000 to 0xFFFF) and doesn't support
             * "supplementary characters" (characters whose code points are
             * greater than 0xFFFF)
             * Java 2 represents these characters as a pair of char (16-bit)
             * values, the first from the high-surrogates range (0xD800-0xDBFF),
             * the second from the low-surrogates range (0xDC00-0xDFFF). Then
             * they are encoded as usual UTF-8 characters in six bytes.
             * Standard UTF-8 representation uses four bytes for supplementary
             * characters.
             */

            $str_val = substr($this->_data, $this->_position, $strlen);
            $this->_position += $strlen;

            for ($count = 0; $count < $strlen; $count++ ) {
                if (( ord($str_val[$count]) & 0xC0 ) == 0xC0) {
                    $addBytes = 1;
                    if (ord($str_val[$count]) & 0x20 ) {
                        $addBytes++;

                        // Never used. Java2 doesn't encode strings in four bytes
                        if (ord($str_val[$count]) & 0x10 ) {
                            $addBytes++;
                        }
                    }
                    $str_val .= substr($this->_data, $this->_position, $addBytes);
                    $this->_position += $addBytes;
                    $strlen          += $addBytes;

                    // Check for null character. Java2 encodes null character
                    // in two bytes.
                    if (ord($str_val[$count])   == 0xC0 &&
                        ord($str_val[$count+1]) == 0x80   ) {
                        $str_val[$count] = 0;
                        $str_val = substr($str_val,0,$count+1)
                                 . substr($str_val,$count+2);
                    }
                    $count += $addBytes;
                }
            }

            return $str_val;
        }
    }

    /**
     * Writes a string to the end of file.
     *
     * @param string $str
     * @throws Zend_Search_Lucene_Exception
     */
    public function writeString($str)
    {
        /**
         * This implementation supports only Basic Multilingual Plane
         * (BMP) characters (from 0x0000 to 0xFFFF) and doesn't support
         * "supplementary characters" (characters whose code points are
         * greater than 0xFFFF)
         * Java 2 represents these characters as a pair of char (16-bit)
         * values, the first from the high-surrogates range (0xD800-0xDBFF),
         * the second from the low-surrogates range (0xDC00-0xDFFF). Then
         * they are encoded as usual UTF-8 characters in six bytes.
         * Standard UTF-8 representation uses four bytes for supplementary
         * characters.
         */

        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        // convert input to a string before iterating string characters
        settype($str, 'string');

        $chars = $strlen = strlen($str);
        $containNullChars = false;

        for ($count = 0; $count < $strlen; $count++ ) {
            /**
             * String is already in Java 2 representation.
             * We should only calculate actual string length and replace
             * \x00 by \xC0\x80
             */
            if ((ord($str[$count]) & 0xC0) == 0xC0) {
                $addBytes = 1;
                if (ord($str[$count]) & 0x20 ) {
                    $addBytes++;

                    // Never used. Java2 doesn't encode strings in four bytes
                    // and we dont't support non-BMP characters
                    if (ord($str[$count]) & 0x10 ) {
                        $addBytes++;
                    }
                }
                $chars -= $addBytes;

                if (ord($str[$count]) == 0 ) {
                    $containNullChars = true;
                }
                $count += $addBytes;
            }
        }

        if ($chars < 0) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Invalid UTF-8 string');
        }

        $this->writeVInt($chars);
        if ($containNullChars) {
            $this->_data .= str_replace($str, "\x00", "\xC0\x80");

        } else {
            $this->_data .= $str;
        }

        $this->_position = strlen($this->_data);
    }


    /**
     * Reads binary data from the current position in the file
     * and advances the file pointer.
     *
     * @return string
     */
    public function readBinary()
    {
        $length = $this->readVInt();
        $returnValue = substr($this->_data, $this->_position, $length);
        $this->_position += $length;
        return $returnValue;
    }
}

