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
 * @package   Zend_TimeSync
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id$
 */

/**
 * Zend_TimeSync_Protocol
 */
#require_once 'Zend/TimeSync/Protocol.php';

/**
 * NTP Protocol handling class
 *
 * @category  Zend
 * @package   Zend_TimeSync
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_TimeSync_Ntp extends Zend_TimeSync_Protocol
{
    /**
     * NTP port number (123) assigned by the Internet Assigned Numbers Authority
     *
     * @var integer
     */
    protected $_port = 123;

    /**
     * NTP class constructor, sets the timeserver and port number
     *
     * @param string  $timeserver Adress of the timeserver to connect to
     * @param integer $port       (Optional) Port for this timeserver
     */
    public function __construct($timeserver, $port = 123)
    {
        $this->_timeserver = 'udp://' . $timeserver;
        if ($port !== null) {
            $this->_port = $port;
        }
    }

    /**
     * Prepare local timestamp for transmission in our request packet
     *
     * NTP timestamps are represented as a 64-bit fixed-point number, in
     * seconds relative to 0000 UT on 1 January 1900.  The integer part is
     * in the first 32 bits and the fraction part in the last 32 bits
     *
     * @return string
     */
    protected function _prepare()
    {
        $frac   = microtime();
        $fracba = ($frac & 0xff000000) >> 24;
        $fracbb = ($frac & 0x00ff0000) >> 16;
        $fracbc = ($frac & 0x0000ff00) >> 8;
        $fracbd = ($frac & 0x000000ff);

        $sec   = (time() + 2208988800);
        $secba = ($sec & 0xff000000) >> 24;
        $secbb = ($sec & 0x00ff0000) >> 16;
        $secbc = ($sec & 0x0000ff00) >> 8;
        $secbd = ($sec & 0x000000ff);

        // Flags
        $nul       = chr(0x00);
        $nulbyte   = $nul . $nul . $nul . $nul;
        $ntppacket = chr(0xd9) . $nul . chr(0x0a) . chr(0xfa);

        /*
         * Root delay
         *
         * Indicates the total roundtrip delay to the primary reference
         * source at the root of the synchronization subnet, in seconds
         */
        $ntppacket .= $nul . $nul . chr(0x1c) . chr(0x9b);

        /*
         * Clock Dispersion
         *
         * Indicates the maximum error relative to the primary reference source at the
         * root of the synchronization subnet, in seconds
         */
        $ntppacket .= $nul . chr(0x08) . chr(0xd7) . chr(0xff);

        /*
         * ReferenceClockID
         *
         * Identifying the particular reference clock
         */
        $ntppacket .= $nulbyte;

        /*
         * The local time, in timestamp format, at the peer when its latest NTP message
         * was sent. Contanis an integer and a fractional part
         */
        $ntppacket .= chr($secba)  . chr($secbb)  . chr($secbc)  . chr($secbd);
        $ntppacket .= chr($fracba) . chr($fracbb) . chr($fracbc) . chr($fracbd);

        /*
         * The local time, in timestamp format, at the peer. Contains an integer
         * and a fractional part.
         */
        $ntppacket .= $nulbyte;
        $ntppacket .= $nulbyte;

        /*
         * This is the local time, in timestamp format, when the latest NTP message from
         * the peer arrived. Contanis an integer and a fractional part.
         */
        $ntppacket .= $nulbyte;
        $ntppacket .= $nulbyte;

        /*
         * The local time, in timestamp format, at which the
         * NTP message departed the sender. Contanis an integer
         * and a fractional part.
         */
        $ntppacket .= chr($secba)  . chr($secbb)  . chr($secbc)  . chr($secbd);
        $ntppacket .= chr($fracba) . chr($fracbb) . chr($fracbc) . chr($fracbd);

        return $ntppacket;
    }

    /**
     * Calculates a 32bit integer
     *
     * @param string $input
     * @return integer
     */
    protected function _getInteger($input)
    {
        $f1  = str_pad(ord($input[0]), 2, '0', STR_PAD_LEFT);
        $f1 .= str_pad(ord($input[1]), 2, '0', STR_PAD_LEFT);
        $f1 .= str_pad(ord($input[2]), 2, '0', STR_PAD_LEFT);
        $f1 .= str_pad(ord($input[3]), 2, '0', STR_PAD_LEFT);
        return (int) $f1;
    }

    /**
     * Calculates a 32bit signed fixed point number
     *
     * @param string $input
     * @return float
     */
    protected function _getFloat($input)
    {
        $f1  = str_pad(ord($input[0]), 2, '0', STR_PAD_LEFT);
        $f1 .= str_pad(ord($input[1]), 2, '0', STR_PAD_LEFT);
        $f1 .= str_pad(ord($input[2]), 2, '0', STR_PAD_LEFT);
        $f1 .= str_pad(ord($input[3]), 2, '0', STR_PAD_LEFT);
        $f2  = $f1 >> 17;
        $f3  = ($f1 & 0x0001FFFF);
        $f1  = $f2 . '.' . $f3;
        return (float) $f1;
    }

    /**
     * Calculates a 64bit timestamp
     *
     * @param string $input
     * @return float
     */
    protected function _getTimestamp($input)
    {
        $f1  = (ord($input[0]) * pow(256, 3));
        $f1 += (ord($input[1]) * pow(256, 2));
        $f1 += (ord($input[2]) * pow(256, 1));
        $f1 += (ord($input[3]));
        $f1 -= 2208988800;

        $f2  = (ord($input[4]) * pow(256, 3));
        $f2 += (ord($input[5]) * pow(256, 2));
        $f2 += (ord($input[6]) * pow(256, 1));
        $f2 += (ord($input[7]));

        return (float) ($f1 . "." . $f2);
    }

    /**
     * Reads the data returned from the timeserver
     *
     * This will return an array with binary data listing:
     *
     * @return array
     * @throws Zend_TimeSync_Exception When timeserver can not be connected
     */
    protected function _read()
    {
        $flags = ord(fread($this->_socket, 1));
        $info  = stream_get_meta_data($this->_socket);

        if ($info['timed_out'] === true) {
            fclose($this->_socket);
            throw new Zend_TimeSync_Exception('could not connect to ' .
                "'$this->_timeserver' on port '$this->_port', reason: 'server timed out'");
        }

        $result = array(
            'flags'          => $flags,
            'stratum'        => ord(fread($this->_socket, 1)),
            'poll'           => ord(fread($this->_socket, 1)),
            'precision'      => ord(fread($this->_socket, 1)),
            'rootdelay'      => $this->_getFloat(fread($this->_socket, 4)),
            'rootdispersion' => $this->_getFloat(fread($this->_socket, 4)),
            'referenceid'    => fread($this->_socket, 4),
            'referencestamp' => $this->_getTimestamp(fread($this->_socket, 8)),
            'originatestamp' => $this->_getTimestamp(fread($this->_socket, 8)),
            'receivestamp'   => $this->_getTimestamp(fread($this->_socket, 8)),
            'transmitstamp'  => $this->_getTimestamp(fread($this->_socket, 8)),
            'clientreceived' => microtime(true)
        );

        $this->_disconnect();
        return $result;
    }

    /**
     * Sends the NTP packet to the server
     *
     * @param  string $data Data to send to the timeserver
     * @return void
     */
    protected function _write($data)
    {
        $this->_connect();

        fwrite($this->_socket, $data);
        stream_set_timeout($this->_socket, Zend_TimeSync::$options['timeout']);
    }

    /**
     * Extracts the binary data returned from the timeserver
     *
     * @param  string|array $binary Data returned from the timeserver
     * @return integer Difference in seconds
     */
    protected function _extract($binary)
    {
        /*
         * Leap Indicator bit 1100 0000
         *
         * Code warning of impending leap-second to be inserted at the end of
         * the last day of the current month.
         */
        $leap = ($binary['flags'] & 0xc0) >> 6;
        switch($leap) {
            case 0:
                $this->_info['leap'] = '0 - no warning';
                break;

            case 1:
                $this->_info['leap'] = '1 - last minute has 61 seconds';
                break;

            case 2:
                $this->_info['leap'] = '2 - last minute has 59 seconds';
                break;

            default:
                $this->_info['leap'] = '3 - not syncronised';
                break;
        }

        /*
         * Version Number bit 0011 1000
         *
         * This should be 3 (RFC 1305)
         */
        $this->_info['version'] = ($binary['flags'] & 0x38) >> 3;

        /*
         * Mode bit 0000 0111
         *
         * Except in broadcast mode, an NTP association is formed when two peers
         * exchange messages and one or both of them create and maintain an
         * instantiation of the protocol machine, called an association.
         */
        $mode = ($binary['flags'] & 0x07);
        switch($mode) {
            case 1:
                $this->_info['mode'] = 'symetric active';
                break;

            case 2:
                $this->_info['mode'] = 'symetric passive';
                break;

            case 3:
                $this->_info['mode'] = 'client';
                break;

            case 4:
                $this->_info['mode'] = 'server';
                break;

            case 5:
                $this->_info['mode'] = 'broadcast';
                break;

            default:
                $this->_info['mode'] = 'reserved';
                break;
        }

        $ntpserviceid = 'Unknown Stratum ' . $binary['stratum'] . ' Service';

        /*
         * Reference Clock Identifier
         *
         * Identifies the particular reference clock.
         */
        $refid = strtoupper($binary['referenceid']);
        switch($binary['stratum']) {
            case 0:
                if (substr($refid, 0, 3) === 'DCN') {
                    $ntpserviceid = 'DCN routing protocol';
                } else if (substr($refid, 0, 4) === 'NIST') {
                    $ntpserviceid = 'NIST public modem';
                } else if (substr($refid, 0, 3) === 'TSP') {
                    $ntpserviceid = 'TSP time protocol';
                } else if (substr($refid, 0, 3) === 'DTS') {
                    $ntpserviceid = 'Digital Time Service';
                }
                break;

            case 1:
                if (substr($refid, 0, 4) === 'ATOM') {
                    $ntpserviceid = 'Atomic Clock (calibrated)';
                } else if (substr($refid, 0, 3) === 'VLF') {
                    $ntpserviceid = 'VLF radio';
                } else if ($refid === 'CALLSIGN') {
                    $ntpserviceid = 'Generic radio';
                } else if (substr($refid, 0, 4) === 'LORC') {
                    $ntpserviceid = 'LORAN-C radionavigation';
                } else if (substr($refid, 0, 4) === 'GOES') {
                    $ntpserviceid = 'GOES UHF environment satellite';
                } else if (substr($refid, 0, 3) === 'GPS') {
                    $ntpserviceid = 'GPS UHF satellite positioning';
                }
                break;

            default:
                $ntpserviceid  = ord(substr($binary['referenceid'], 0, 1));
                $ntpserviceid .= '.';
                $ntpserviceid .= ord(substr($binary['referenceid'], 1, 1));
                $ntpserviceid .= '.';
                $ntpserviceid .= ord(substr($binary['referenceid'], 2, 1));
                $ntpserviceid .= '.';
                $ntpserviceid .= ord(substr($binary['referenceid'], 3, 1));
                break;
        }

        $this->_info['ntpid'] = $ntpserviceid;

        /*
         * Stratum
         *
         * Indicates the stratum level of the local clock
         */
        switch($binary['stratum']) {
            case 0:
                $this->_info['stratum'] = 'undefined';
                break;

            case 1:
                $this->_info['stratum'] = 'primary reference';
                break;

            default:
                $this->_info['stratum'] = 'secondary reference';
                break;
        }

        /*
         * Indicates the total roundtrip delay to the primary reference source at the
         * root of the synchronization subnet, in seconds.
         *
         * Both positive and negative values, depending on clock precision and skew, are
         * possible.
         */
        $this->_info['rootdelay'] = $binary['rootdelay'];

        /*
         * Indicates the maximum error relative to the primary reference source at the
         * root of the synchronization subnet, in seconds.
         *
         * Only positive values greater than zero are possible.
         */
        $this->_info['rootdispersion'] = $binary['rootdispersion'];

        /*
         * The roundtrip delay of the peer clock relative to the local clock
         * over the network path between them, in seconds.
         *
         * Note that this variable can take on both positive and negative values,
         * depending on clock precision and skew-error accumulation.
         */
        $this->_info['roundtrip']  = $binary['receivestamp'];
        $this->_info['roundtrip'] -= $binary['originatestamp'];
        $this->_info['roundtrip'] -= $binary['transmitstamp'];
        $this->_info['roundtrip'] += $binary['clientreceived'];
        $this->_info['roundtrip'] /= 2;

        // The offset of the peer clock relative to the local clock, in seconds.
        $this->_info['offset']  = $binary['receivestamp'];
        $this->_info['offset'] -= $binary['originatestamp'];
        $this->_info['offset'] += $binary['transmitstamp'];
        $this->_info['offset'] -= $binary['clientreceived'];
        $this->_info['offset'] /= 2;
        $time = (time() - $this->_info['offset']);

        return $time;
    }
}
