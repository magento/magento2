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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: File.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Search_Lucene_Storage_File
{
    /**
     * Reads $length number of bytes at the current position in the
     * file and advances the file pointer.
     *
     * @param integer $length
     * @return string
     */
    abstract protected function _fread($length=1);


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
    abstract public function seek($offset, $whence=SEEK_SET);

    /**
     * Get file position.
     *
     * @return integer
     */
    abstract public function tell();

    /**
     * Flush output.
     *
     * Returns true on success or false on failure.
     *
     * @return boolean
     */
    abstract public function flush();

    /**
     * Writes $length number of bytes (all, if $length===null) to the end
     * of the file.
     *
     * @param string $data
     * @param integer $length
     */
    abstract protected function _fwrite($data, $length=null);

    /**
     * Lock file
     *
     * Lock type may be a LOCK_SH (shared lock) or a LOCK_EX (exclusive lock)
     *
     * @param integer $lockType
     * @return boolean
     */
    abstract public function lock($lockType, $nonBlockinLock = false);

    /**
     * Unlock file
     */
    abstract public function unlock();

    /**
     * Reads a byte from the current position in the file
     * and advances the file pointer.
     *
     * @return integer
     */
    public function readByte()
    {
        return ord($this->_fread(1));
    }

    /**
     * Writes a byte to the end of the file.
     *
     * @param integer $byte
     */
    public function writeByte($byte)
    {
        return $this->_fwrite(chr($byte), 1);
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
        return $this->_fread($num);
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
        $this->_fwrite($data, $num);
    }


    /**
     * Reads an integer from the current position in the file
     * and advances the file pointer.
     *
     * @return integer
     */
    public function readInt()
    {
        $str = $this->_fread(4);

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
        settype($value, 'integer');
        $this->_fwrite( chr($value>>24 & 0xFF) .
                        chr($value>>16 & 0xFF) .
                        chr($value>>8  & 0xFF) .
                        chr($value     & 0xFF),   4  );
    }


    /**
     * Returns a long integer from the current position in the file
     * and advances the file pointer.
     *
     * @return integer|float
     * @throws Zend_Search_Lucene_Exception
     */
    public function readLong()
    {
        /**
         * Check, that we work in 64-bit mode.
         * fseek() uses long for offset. Thus, largest index segment file size in 32bit mode is 2Gb
         */
        if (PHP_INT_SIZE > 4) {
            $str = $this->_fread(8);

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
        /**
         * Check, that we work in 64-bit mode.
         * fseek() and ftell() use long for offset. Thus, largest index segment file size in 32bit mode is 2Gb
         */
        if (PHP_INT_SIZE > 4) {
            settype($value, 'integer');
            $this->_fwrite( chr($value>>56 & 0xFF) .
                            chr($value>>48 & 0xFF) .
                            chr($value>>40 & 0xFF) .
                            chr($value>>32 & 0xFF) .
                            chr($value>>24 & 0xFF) .
                            chr($value>>16 & 0xFF) .
                            chr($value>>8  & 0xFF) .
                            chr($value     & 0xFF),   8  );
        } else {
            $this->writeLong32Bit($value);
        }
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
        $nextByte = ord($this->_fread(1));
        $val = $nextByte & 0x7F;

        for ($shift=7; ($nextByte & 0x80) != 0; $shift += 7) {
            $nextByte = ord($this->_fread(1));
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
        settype($value, 'integer');
        while ($value > 0x7F) {
            $this->_fwrite(chr( ($value & 0x7F)|0x80 ));
            $value >>= 7;
        }
        $this->_fwrite(chr($value));
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

            $str_val = $this->_fread($strlen);

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
                    $str_val .= $this->_fread($addBytes);
                    $strlen += $addBytes;

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
            $this->_fwrite(str_replace($str, "\x00", "\xC0\x80"));
        } else {
            $this->_fwrite($str);
        }
    }


    /**
     * Reads binary data from the current position in the file
     * and advances the file pointer.
     *
     * @return string
     */
    public function readBinary()
    {
        return $this->_fread($this->readVInt());
    }
}
