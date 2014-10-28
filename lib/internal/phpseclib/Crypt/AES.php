<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Pure-PHP implementation of AES.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.
 *
 * PHP versions 4 and 5
 *
 * If {@link Crypt_AES::setKeyLength() setKeyLength()} isn't called, it'll be calculated from
 * {@link Crypt_AES::setKey() setKey()}.  ie. if the key is 128-bits, the key length will be 128-bits.  If it's 136-bits
 * it'll be null-padded to 160-bits and 160 bits will be the key length until {@link Crypt_Rijndael::setKey() setKey()}
 * is called, again, at which point, it'll be recalculated.
 *
 * Since Crypt_AES extends Crypt_Rijndael, some functions are available to be called that, in the context of AES, don't
 * make a whole lot of sense.  {@link Crypt_AES::setBlockLength() setBlockLength()}, for instance.  Calling that function,
 * however possible, won't do anything (AES has a fixed block length whereas Rijndael has a variable one).
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include('Crypt/AES.php');
 *
 *    $aes = new Crypt_AES();
 *
 *    $aes->setKey('abcdefghijklmnop');
 *
 *    $size = 10 * 1024;
 *    $plaintext = '';
 *    for ($i = 0; $i < $size; $i++) {
 *        $plaintext.= 'a';
 *    }
 *
 *    echo $aes->decrypt($aes->encrypt($plaintext));
 * ?>
 * </code>
 *
 * LICENSE: This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston,
 * MA  02111-1307  USA
 *
 * @category   Crypt
 * @package    Crypt_AES
 * @author     Jim Wigginton <terrafrost@php.net>
 * @copyright  MMVIII Jim Wigginton
 * @license    http://www.gnu.org/licenses/lgpl.txt
 * @version    $Id: AES.php,v 1.7 2010/02/09 06:10:25 terrafrost Exp $
 * @link       http://phpseclib.sourceforge.net
 */

/**
 * Include Crypt_Rijndael
 */
require_once 'Rijndael.php';

/**#@+
 * @access public
 * @see Crypt_AES::encrypt()
 * @see Crypt_AES::decrypt()
 */
/**
 * Encrypt / decrypt using the Counter mode.
 *
 * Set to -1 since that's what Crypt/Random.php uses to index the CTR mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Counter_.28CTR.29
 */
define('CRYPT_AES_MODE_CTR', -1);
/**
 * Encrypt / decrypt using the Electronic Code Book mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Electronic_codebook_.28ECB.29
 */
define('CRYPT_AES_MODE_ECB', 1);
/**
 * Encrypt / decrypt using the Code Book Chaining mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher-block_chaining_.28CBC.29
 */
define('CRYPT_AES_MODE_CBC', 2);
/**#@-*/

/**#@+
 * @access private
 * @see Crypt_AES::Crypt_AES()
 */
/**
 * Toggles the internal implementation
 */
define('CRYPT_AES_MODE_INTERNAL', 1);
/**
 * Toggles the mcrypt implementation
 */
define('CRYPT_AES_MODE_MCRYPT', 2);
/**#@-*/

/**
 * Pure-PHP implementation of AES.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @version 0.1.0
 * @access  public
 * @package Crypt_AES
 */
class Crypt_AES extends Crypt_Rijndael {
    /**
     * mcrypt resource for encryption
     *
     * The mcrypt resource can be recreated every time something needs to be created or it can be created just once.
     * Since mcrypt operates in continuous mode, by default, it'll need to be recreated when in non-continuous mode.
     *
     * @see Crypt_AES::encrypt()
     * @var String
     * @access private
     */
    var $enmcrypt;

    /**
     * mcrypt resource for decryption
     *
     * The mcrypt resource can be recreated every time something needs to be created or it can be created just once.
     * Since mcrypt operates in continuous mode, by default, it'll need to be recreated when in non-continuous mode.
     *
     * @see Crypt_AES::decrypt()
     * @var String
     * @access private
     */
    var $demcrypt;

    /**
     * Default Constructor.
     *
     * Determines whether or not the mcrypt extension should be used.  $mode should only, at present, be
     * CRYPT_AES_MODE_ECB or CRYPT_AES_MODE_CBC.  If not explictly set, CRYPT_AES_MODE_CBC will be used.
     *
     * @param optional Integer $mode
     * @return Crypt_AES
     * @access public
     */
    function Crypt_AES($mode = CRYPT_AES_MODE_CBC)
    {
        if ( !defined('CRYPT_AES_MODE') ) {
            switch (true) {
                case extension_loaded('mcrypt'):
                    // i'd check to see if aes was supported, by doing in_array('des', mcrypt_list_algorithms('')),
                    // but since that can be changed after the object has been created, there doesn't seem to be
                    // a lot of point...
                    define('CRYPT_AES_MODE', CRYPT_AES_MODE_MCRYPT);
                    break;
                default:
                    define('CRYPT_AES_MODE', CRYPT_AES_MODE_INTERNAL);
            }
        }

        switch ( CRYPT_AES_MODE ) {
            case CRYPT_AES_MODE_MCRYPT:
                switch ($mode) {
                    case CRYPT_AES_MODE_ECB:
                        $this->mode = MCRYPT_MODE_ECB;
                        break;
                    case CRYPT_AES_MODE_CTR:
                        // ctr doesn't have a constant associated with it even though it appears to be fairly widely
                        // supported.  in lieu of knowing just how widely supported it is, i've, for now, opted not to
                        // include a compatibility layer.  the layer has been implemented but, for now, is commented out.
                        $this->mode = 'ctr';
                        //$this->mode = in_array('ctr', mcrypt_list_modes()) ? 'ctr' : CRYPT_AES_MODE_CTR;
                        break;
                    case CRYPT_AES_MODE_CBC:
                    default:
                        $this->mode = MCRYPT_MODE_CBC;
                }

                break;
            default:
                switch ($mode) {
                    case CRYPT_AES_MODE_ECB:
                        $this->mode = CRYPT_RIJNDAEL_MODE_ECB;
                        break;
                    case CRYPT_AES_MODE_CTR:
                        $this->mode = CRYPT_RIJNDAEL_MODE_CTR;
                        break;
                    case CRYPT_AES_MODE_CBC:
                    default:
                        $this->mode = CRYPT_RIJNDAEL_MODE_CBC;
                }
        }

        if (CRYPT_AES_MODE == CRYPT_AES_MODE_INTERNAL) {
            parent::Crypt_Rijndael($this->mode);
        }
    }

    /**
     * Dummy function
     *
     * Since Crypt_AES extends Crypt_Rijndael, this function is, technically, available, but it doesn't do anything.
     *
     * @access public
     * @param Integer $length
     */
    function setBlockLength($length)
    {
        return;
    }

    /**
     * Encrypts a message.
     *
     * $plaintext will be padded with up to 16 additional bytes.  Other AES implementations may or may not pad in the
     * same manner.  Other common approaches to padding and the reasons why it's necessary are discussed in the following
     * URL:
     *
     * {@link http://www.di-mgt.com.au/cryptopad.html http://www.di-mgt.com.au/cryptopad.html}
     *
     * An alternative to padding is to, separately, send the length of the file.  This is what SSH, in fact, does.
     * strlen($plaintext) will still need to be a multiple of 16, however, arbitrary values can be added to make it that
     * length.
     *
     * @see Crypt_AES::decrypt()
     * @access public
     * @param String $plaintext
     */
    function encrypt($plaintext)
    {
        if ( CRYPT_AES_MODE == CRYPT_AES_MODE_MCRYPT ) {
            $this->_mcryptSetup();
            /*
            if ($this->mode == CRYPT_AES_MODE_CTR) {
                $iv = $this->encryptIV;
                $xor = mcrypt_generic($this->enmcrypt, $this->_generate_xor(strlen($plaintext), $iv));
                $ciphertext = $plaintext ^ $xor;
                if ($this->continuousBuffer) {
                    $this->encryptIV = $iv;
                }
                return $ciphertext;
            }
            */

            if ($this->mode != 'ctr') {
                $plaintext = $this->_pad($plaintext);
            }

            $ciphertext = mcrypt_generic($this->enmcrypt, $plaintext);

            if (!$this->continuousBuffer) {
                mcrypt_generic_init($this->enmcrypt, $this->key, $this->iv);
            }

            return $ciphertext;
        }

        return parent::encrypt($plaintext);
    }

    /**
     * Decrypts a message.
     *
     * If strlen($ciphertext) is not a multiple of 16, null bytes will be added to the end of the string until it is.
     *
     * @see Crypt_AES::encrypt()
     * @access public
     * @param String $ciphertext
     */
    function decrypt($ciphertext)
    {
        if ( CRYPT_AES_MODE == CRYPT_AES_MODE_MCRYPT ) {
            $this->_mcryptSetup();
            /*
            if ($this->mode == CRYPT_AES_MODE_CTR) {
                $iv = $this->decryptIV;
                $xor = mcrypt_generic($this->enmcrypt, $this->_generate_xor(strlen($ciphertext), $iv));
                $plaintext = $ciphertext ^ $xor;
                if ($this->continuousBuffer) {
                    $this->decryptIV = $iv;
                }
                return $plaintext;
            }
            */

            if ($this->mode != 'ctr') {
                // we pad with chr(0) since that's what mcrypt_generic does.  to quote from http://php.net/function.mcrypt-generic :
                // "The data is padded with "\0" to make sure the length of the data is n * blocksize."
                $ciphertext = str_pad($ciphertext, (strlen($ciphertext) + 15) & 0xFFFFFFF0, chr(0));
            }

            $plaintext = mdecrypt_generic($this->demcrypt, $ciphertext);

            if (!$this->continuousBuffer) {
                mcrypt_generic_init($this->demcrypt, $this->key, $this->iv);
            }

            return $this->mode != 'ctr' ? $this->_unpad($plaintext) : $plaintext;
        }

        return parent::decrypt($ciphertext);
    }

    /**
     * Setup mcrypt
     *
     * Validates all the variables.
     *
     * @access private
     */
    function _mcryptSetup()
    {
        if (!$this->changed) {
            return;
        }

        if (!$this->explicit_key_length) {
            // this just copied from Crypt_Rijndael::_setup()
            $length = strlen($this->key) >> 2;
            if ($length > 8) {
                $length = 8;
            } else if ($length < 4) {
                $length = 4;
            }
            $this->Nk = $length;
            $this->key_size = $length << 2;
        }

        switch ($this->Nk) {
            case 4: // 128
                $this->key_size = 16;
                break;
            case 5: // 160
            case 6: // 192
                $this->key_size = 24;
                break;
            case 7: // 224
            case 8: // 256
                $this->key_size = 32;
        }

        $this->key = substr($this->key, 0, $this->key_size);
        $this->encryptIV = $this->decryptIV = $this->iv = str_pad(substr($this->iv, 0, 16), 16, chr(0));

        if (!isset($this->enmcrypt)) {
            $mode = $this->mode;
            //$mode = $this->mode == CRYPT_AES_MODE_CTR ? MCRYPT_MODE_ECB : $this->mode;

            $this->demcrypt = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', $mode, '');
            $this->enmcrypt = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', $mode, '');
        } // else should mcrypt_generic_deinit be called?

        mcrypt_generic_init($this->demcrypt, $this->key, $this->iv);
        mcrypt_generic_init($this->enmcrypt, $this->key, $this->iv);

        $this->changed = false;
    }

    /**
     * Encrypts a block
     *
     * Optimized over Crypt_Rijndael's implementation by means of loop unrolling.
     *
     * @see Crypt_Rijndael::_encryptBlock()
     * @access private
     * @param String $in
     * @return String
     */
    function _encryptBlock($in)
    {
        $state = unpack('N*word', $in);

        $Nr = $this->Nr;
        $w = $this->w;
        $t0 = $this->t0;
        $t1 = $this->t1;
        $t2 = $this->t2;
        $t3 = $this->t3;

        // addRoundKey and reindex $state
        $state = array(
            $state['word1'] ^ $w[0][0],
            $state['word2'] ^ $w[0][1],
            $state['word3'] ^ $w[0][2],
            $state['word4'] ^ $w[0][3]
        );

        // shiftRows + subWord + mixColumns + addRoundKey
        // we could loop unroll this and use if statements to do more rounds as necessary, but, in my tests, that yields
        // only a marginal improvement.  since that also, imho, hinders the readability of the code, i've opted not to do it.
        for ($round = 1; $round < $this->Nr; $round++) {
            $state = array(
                $t0[$state[0] & 0xFF000000] ^ $t1[$state[1] & 0x00FF0000] ^ $t2[$state[2] & 0x0000FF00] ^ $t3[$state[3] & 0x000000FF] ^ $w[$round][0],
                $t0[$state[1] & 0xFF000000] ^ $t1[$state[2] & 0x00FF0000] ^ $t2[$state[3] & 0x0000FF00] ^ $t3[$state[0] & 0x000000FF] ^ $w[$round][1],
                $t0[$state[2] & 0xFF000000] ^ $t1[$state[3] & 0x00FF0000] ^ $t2[$state[0] & 0x0000FF00] ^ $t3[$state[1] & 0x000000FF] ^ $w[$round][2],
                $t0[$state[3] & 0xFF000000] ^ $t1[$state[0] & 0x00FF0000] ^ $t2[$state[1] & 0x0000FF00] ^ $t3[$state[2] & 0x000000FF] ^ $w[$round][3]
            );

        }

        // subWord
        $state = array(
            $this->_subWord($state[0]),
            $this->_subWord($state[1]),
            $this->_subWord($state[2]),
            $this->_subWord($state[3])
        );

        // shiftRows + addRoundKey
        $state = array(
            ($state[0] & 0xFF000000) ^ ($state[1] & 0x00FF0000) ^ ($state[2] & 0x0000FF00) ^ ($state[3] & 0x000000FF) ^ $this->w[$this->Nr][0],
            ($state[1] & 0xFF000000) ^ ($state[2] & 0x00FF0000) ^ ($state[3] & 0x0000FF00) ^ ($state[0] & 0x000000FF) ^ $this->w[$this->Nr][1],
            ($state[2] & 0xFF000000) ^ ($state[3] & 0x00FF0000) ^ ($state[0] & 0x0000FF00) ^ ($state[1] & 0x000000FF) ^ $this->w[$this->Nr][2],
            ($state[3] & 0xFF000000) ^ ($state[0] & 0x00FF0000) ^ ($state[1] & 0x0000FF00) ^ ($state[2] & 0x000000FF) ^ $this->w[$this->Nr][3]
        );

        return pack('N*', $state[0], $state[1], $state[2], $state[3]);
    }

    /**
     * Decrypts a block
     *
     * Optimized over Crypt_Rijndael's implementation by means of loop unrolling.
     *
     * @see Crypt_Rijndael::_decryptBlock()
     * @access private
     * @param String $in
     * @return String
     */
    function _decryptBlock($in)
    {
        $state = unpack('N*word', $in);

        $Nr = $this->Nr;
        $dw = $this->dw;
        $dt0 = $this->dt0;
        $dt1 = $this->dt1;
        $dt2 = $this->dt2;
        $dt3 = $this->dt3;

        // addRoundKey and reindex $state
        $state = array(
            $state['word1'] ^ $dw[$this->Nr][0],
            $state['word2'] ^ $dw[$this->Nr][1],
            $state['word3'] ^ $dw[$this->Nr][2],
            $state['word4'] ^ $dw[$this->Nr][3]
        );


        // invShiftRows + invSubBytes + invMixColumns + addRoundKey
        for ($round = $this->Nr - 1; $round > 0; $round--) {
            $state = array(
                $dt0[$state[0] & 0xFF000000] ^ $dt1[$state[3] & 0x00FF0000] ^ $dt2[$state[2] & 0x0000FF00] ^ $dt3[$state[1] & 0x000000FF] ^ $dw[$round][0],
                $dt0[$state[1] & 0xFF000000] ^ $dt1[$state[0] & 0x00FF0000] ^ $dt2[$state[3] & 0x0000FF00] ^ $dt3[$state[2] & 0x000000FF] ^ $dw[$round][1],
                $dt0[$state[2] & 0xFF000000] ^ $dt1[$state[1] & 0x00FF0000] ^ $dt2[$state[0] & 0x0000FF00] ^ $dt3[$state[3] & 0x000000FF] ^ $dw[$round][2],
                $dt0[$state[3] & 0xFF000000] ^ $dt1[$state[2] & 0x00FF0000] ^ $dt2[$state[1] & 0x0000FF00] ^ $dt3[$state[0] & 0x000000FF] ^ $dw[$round][3]
            );
        }

        // invShiftRows + invSubWord + addRoundKey
        $state = array(
            $this->_invSubWord(($state[0] & 0xFF000000) ^ ($state[3] & 0x00FF0000) ^ ($state[2] & 0x0000FF00) ^ ($state[1] & 0x000000FF)) ^ $dw[0][0],
            $this->_invSubWord(($state[1] & 0xFF000000) ^ ($state[0] & 0x00FF0000) ^ ($state[3] & 0x0000FF00) ^ ($state[2] & 0x000000FF)) ^ $dw[0][1],
            $this->_invSubWord(($state[2] & 0xFF000000) ^ ($state[1] & 0x00FF0000) ^ ($state[0] & 0x0000FF00) ^ ($state[3] & 0x000000FF)) ^ $dw[0][2],
            $this->_invSubWord(($state[3] & 0xFF000000) ^ ($state[2] & 0x00FF0000) ^ ($state[1] & 0x0000FF00) ^ ($state[0] & 0x000000FF)) ^ $dw[0][3]
        );

        return pack('N*', $state[0], $state[1], $state[2], $state[3]);
    }
}

// vim: ts=4:sw=4:et:
// vim6: fdl=1:
