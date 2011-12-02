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
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PythonPickle.php 21187 2010-02-24 01:22:01Z stas $
 */

/** @see Zend_Serializer_Adapter_AdapterAbstract */
#require_once 'Zend/Serializer/Adapter/AdapterAbstract.php';

/**
 * @link       http://www.python.org
 * @see        Phython3.1/Lib/pickle.py
 * @see        Phython3.1/Modules/_pickle.c
 * @link       http://pickle-js.googlecode.com
 * @category   Zend
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Serializer_Adapter_PythonPickle extends Zend_Serializer_Adapter_AdapterAbstract
{
    /* Pickle opcodes. See pickletools.py for extensive docs.  The listing
       here is in kind-of alphabetical order of 1-character pickle code.
       pickletools groups them by purpose. */
    const OP_MARK            = '(';     // push special markobject on stack
    const OP_STOP            = '.';     // every pickle ends with STOP
    const OP_POP             = '0';     // discard topmost stack item
    const OP_POP_MARK        = '1';     // discard stack top through topmost markobject
    const OP_DUP             = '2';     // duplicate top stack item
    const OP_FLOAT           = 'F';     // push float object; decimal string argument
    const OP_INT             = 'I';     // push integer or bool; decimal string argument
    const OP_BININT          = 'J';     // push four-byte signed int
    const OP_BININT1         = 'K';     // push 1-byte unsigned int
    const OP_LONG            = 'L';     // push long; decimal string argument
    const OP_BININT2         = 'M';     // push 2-byte unsigned int
    const OP_NONE            = 'N';     // push None
    const OP_PERSID          = 'P';     // push persistent object; id is taken from string arg
    const OP_BINPERSID       = 'Q';     //  "       "         "  ;  "  "   "     "  stack
    const OP_REDUCE          = 'R';     // apply callable to argtuple, both on stack
    const OP_STRING          = 'S';     // push string; NL-terminated string argument
    const OP_BINSTRING       = 'T';     // push string; counted binary string argument
    const OP_SHORT_BINSTRING = 'U';     //  "     "   ;    "      "       "      " < 256 bytes
    const OP_UNICODE         = 'V';     // push Unicode string; raw-unicode-escaped'd argument
    const OP_BINUNICODE      = 'X';     //   "     "       "  ; counted UTF-8 string argument
    const OP_APPEND          = 'a';     // append stack top to list below it
    const OP_BUILD           = 'b';     // call __setstate__ or __dict__.update()
    const OP_GLOBAL          = 'c';     // push self.find_class(modname, name); 2 string args
    const OP_DICT            = 'd';     // build a dict from stack items
    const OP_EMPTY_DICT      = '}';     // push empty dict
    const OP_APPENDS         = 'e';     // extend list on stack by topmost stack slice
    const OP_GET             = 'g';     // push item from memo on stack; index is string arg
    const OP_BINGET          = 'h';     //   "    "    "    "   "   "  ;   "    " 1-byte arg
    const OP_INST            = 'i';     // build & push class instance
    const OP_LONG_BINGET     = 'j';     // push item from memo on stack; index is 4-byte arg
    const OP_LIST            = 'l';     // build list from topmost stack items
    const OP_EMPTY_LIST      = ']';     // push empty list
    const OP_OBJ             = 'o';     // build & push class instance
    const OP_PUT             = 'p';     // store stack top in memo; index is string arg
    const OP_BINPUT          = 'q';     //   "     "    "   "   " ;   "    " 1-byte arg
    const OP_LONG_BINPUT     = 'r';     //   "     "    "   "   " ;   "    " 4-byte arg
    const OP_SETITEM         = 's';     // add key+value pair to dict
    const OP_TUPLE           = 't';     // build tuple from topmost stack items
    const OP_EMPTY_TUPLE     = ')';     // push empty tuple
    const OP_SETITEMS        = 'u';     // modify dict by adding topmost key+value pairs
    const OP_BINFLOAT        = 'G';     // push float; arg is 8-byte float encoding

    /* Protocol 2 */
    const OP_PROTO           = "\x80";  // identify pickle protocol
    const OP_NEWOBJ          = "\x81";  // build object by applying cls.__new__ to argtuple
    const OP_EXT1            = "\x82";  // push object from extension registry; 1-byte index
    const OP_EXT2            = "\x83";  // ditto, but 2-byte index
    const OP_EXT4            = "\x84";  // ditto, but 4-byte index
    const OP_TUPLE1          = "\x85";  // build 1-tuple from stack top
    const OP_TUPLE2          = "\x86";  // build 2-tuple from two topmost stack items
    const OP_TUPLE3          = "\x87";  // build 3-tuple from three topmost stack items
    const OP_NEWTRUE         = "\x88";  // push True
    const OP_NEWFALSE        = "\x89";  // push False
    const OP_LONG1           = "\x8a";  // push long from < 256 bytes
    const OP_LONG4           = "\x8b";  // push really big long

    /* Protocol 3 (Python 3.x) */
    const OP_BINBYTES        = 'B';     // push bytes; counted binary string argument
    const OP_SHORT_BINBYTES  = 'C';     //  "     "   ;    "      "       "      " < 256 bytes

    /**
     * @var bool Whether or not this is a PHP 6 binary
     */
    protected static $_isPhp6 = null;

    /**
     * @var bool Whether or not the system is little-endian
     */
    protected static $_isLittleEndian = null;

    /**
     * @var array Strings representing quotes
     */
    protected static $_quoteString = array(
        '\\' => '\\\\',
        "\x00" => '\\x00', "\x01" => '\\x01', "\x02" => '\\x02', "\x03" => '\\x03',
        "\x04" => '\\x04', "\x05" => '\\x05', "\x06" => '\\x06', "\x07" => '\\x07',
        "\x08" => '\\x08', "\x09" => '\\t',   "\x0a" => '\\n',   "\x0b" => '\\x0b',
        "\x0c" => '\\x0c', "\x0d" => '\\r',   "\x0e" => '\\x0e', "\x0f" => '\\x0f',
        "\x10" => '\\x10', "\x11" => '\\x11', "\x12" => '\\x12', "\x13" => '\\x13',
        "\x14" => '\\x14', "\x15" => '\\x15', "\x16" => '\\x16', "\x17" => '\\x17',
        "\x18" => '\\x18', "\x19" => '\\x19', "\x1a" => '\\x1a', "\x1b" => '\\x1b',
        "\x1c" => '\\x1c', "\x1d" => '\\x1d', "\x1e" => '\\x1e', "\x1f" => '\\x1f',
        "\xff" => '\\xff'
    );

    /**
     * @var array Default options
     */
    protected $_options = array(
        'protocol'           => 0,
    );

    // process vars
    protected $_protocol           = 0;
    protected $_binary             = false;
    protected $_memo               = array();
    protected $_pickle             = '';
    protected $_pickleLen          = 0;
    protected $_pos                = 0;
    protected $_stack              = array();
    protected $_marker             = null;

    /**
     * Constructor
     *
     * @link Zend_Serializer_Adapter_AdapterAbstract::__construct()
     */
    public function __construct($opts=array())
    {
        parent::__construct($opts);

        // init
        if (self::$_isLittleEndian === null) {
            self::$_isLittleEndian = (pack('l', 1) === "\x01\x00\x00\x00");
        }
        if (self::$_isPhp6 === null) {
            self::$_isPhp6 = !version_compare(PHP_VERSION, '6.0.0', '<');
        }

        $this->_marker = new stdClass();
    }

    /**
     * Set an option
     *
     * @link   Zend_Serializer_Adapter_AdapterAbstract::setOption()
     * @param  string $name
     * @param  mixed $value
     * @return Zend_Serializer_Adapter_PythonPickle
     */
    public function setOption($name, $value)
    {
        switch ($name) {
            case 'protocol':
                $value = $this->_checkProtocolNumber($value);
                break;
        }

        return parent::setOption($name, $value);
    }

    /**
     * Check and normalize pickle protocol number
     *
     * @param  int $number
     * @return int
     * @throws Zend_Serializer_Exception
     */
    protected function _checkProtocolNumber($number)
    {
        $int = (int) $number;
        if ($int < 0 || $int > 3) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('Invalid or unknown protocol version "'.$number.'"');
        }
        return $int;
    }

    /* serialize */

    /**
     * Serialize PHP to PythonPickle format
     *
     * @param  mixed $value
     * @param  array $opts
     * @return string
     */
    public function serialize($value, array $opts = array())
    {
        $opts = $opts + $this->_options;

        $this->_protocol = $this->_checkProtocolNumber($opts['protocol']);
        $this->_binary   = $this->_protocol != 0;

        // clear process vars before serializing
        $this->_memo   = array();
        $this->_pickle = '';

        // write
        if ($this->_protocol >= 2) {
            $this->_writeProto($this->_protocol);
        }
        $this->_write($value);
        $this->_writeStop();

        // clear process vars after serializing
        $this->_memo = array();
        $pickle = $this->_pickle;
        $this->_pickle = '';

        return $pickle;
    }

    /**
     * Write a value
     *
     * @param  mixed $value
     * @return void
     * @throws Zend_Serializer_Exception on invalid or unrecognized value type
     */
    protected function _write($value)
    {
        if ($value === null) {
            $this->_writeNull();
        } elseif ($value === true) {
            $this->_writeTrue();
        } elseif ($value === false) {
            $this->_writeFalse();
        } elseif (is_int($value)) {
            $this->_writeInt($value);
        } elseif (is_float($value)) {
            $this->_writeFloat($value);
        } elseif (is_string($value)) {
            // TODO: write unicode / binary
            $this->_writeString($value);
        } elseif (is_array($value)) {
            if ($this->_isArrayAssoc($value)) {
                $this->_writeArrayDict($value);
            } else {
                $this->_writeArrayList($value);
            }
        } elseif (is_object($value)) {
            $this->_writeObject($value);
        } else {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception(
                'PHP-Type "'.gettype($value).'" isn\'t serializable with '.get_class($this)
            );
        }
    }

    /**
     * Write pickle protocol
     *
     * @param  int $protocol
     * @return void
     */
    protected function _writeProto($protocol)
    {
        $this->_pickle .= self::OP_PROTO . $protocol;
    }

    /**
     * Write a get
     *
     * @param  int $id Id of memo
     * @return void
     */
    protected function _writeGet($id)
    {
        if ($this->_binary) {
            if ($id <= 0xff) {
                // BINGET + chr(i)
                $this->_pickle .= self::OP_BINGET . chr($id);
            } else {
                // LONG_BINGET + pack("<i", i)
                $idBin = pack('l', $id);
                if (self::$_isLittleEndian === false) {
                    $idBin = strrev($bin);
                }
                $this->_pickle .= self::OP_LONG_BINGET . $idBin;
            }
        } else {
            $this->_pickle .= self::OP_GET . $id . "\r\n";
        }
    }

    /**
     * Write a put
     *
     * @param  int $id Id of memo
     * @return void
     */
    protected function _writePut($id)
    {
        if ($this->_binary) {
            if ($id <= 0xff) {
                // BINPUT + chr(i)
                $this->_pickle .= self::OP_BINPUT . chr($id);
            } else {
                // LONG_BINPUT + pack("<i", i)
                $idBin = pack('l', $id);
                if (self::$_isLittleEndian === false) {
                    $idBin = strrev($bin);
                }
                $this->_pickle .= self::OP_LONG_BINPUT . $idBin;
            }
        } else {
            $this->_pickle .= self::OP_PUT . $id . "\r\n";
        }
    }

    /**
     * Write a null as None
     *
     * @return void
     */
    protected function _writeNull()
    {
        $this->_pickle .= self::OP_NONE;
    }

    /**
     * Write a boolean true
     *
     * @return void
     */
    protected function _writeTrue()
    {
        if ($this->_protocol >= 2) {
            $this->_pickle .= self::OP_NEWTRUE;
        } else {
            $this->_pickle .= self::OP_INT . "01\r\n";
        }
    }

    /**
     * Write a boolean false
     *
     * @return void
     */
    protected function _writeFalse()
    {
        if ($this->_protocol >= 2) {
            $this->_pickle .= self::OP_NEWFALSE;
        } else {
            $this->_pickle .= self::OP_INT . "00\r\n";
        }
    }

    /**
     * Write an integer value
     *
     * @param  int $value
     * @return void
     */
    protected function _writeInt($value)
    {
        if ($this->_binary) {
            if ($value >= 0) {
                if ($value <= 0xff) {
                    // self.write(BININT1 + chr(obj))
                    $this->_pickle .= self::OP_BININT1 . chr($value);
                } elseif ($value <= 0xffff) {
                    // self.write("%c%c%c" % (BININT2, obj&0xff, obj>>8))
                    $this->_pickle .= self::OP_BININT2 . pack('v', $value);
                }
                return;
            }

            // Next check for 4-byte signed ints:
            $highBits = $value >> 31;  // note that Python shift sign-extends
            if ($highBits == 0 || $highBits == -1) {
                // All high bits are copies of bit 2**31, so the value
                // fits in a 4-byte signed int.
                // self.write(BININT + pack("<i", obj))
                $bin = pack('l', $value);
                if (self::$_isLittleEndian === false) {
                    $bin = strrev($bin);
                }
                $this->_pickle .= self::OP_BININT . $bin;
                return;
            }
        }

        $this->_pickle .= self::OP_INT . $value . "\r\n";
    }

    /**
     * Write a float value
     *
     * @param  float $value
     * @return void
     */
    protected function _writeFloat($value)
    {
        if ($this->_binary) {
            // self.write(BINFLOAT + pack('>d', obj))
            $bin = pack('d', $value);
            if (self::$_isLittleEndian === true) {
                $bin = strrev($bin);
            }
            $this->_pickle .= self::OP_BINFLOAT . $bin;
        } else {
            $this->_pickle .= self::OP_FLOAT . $value . "\r\n";
        }
    }

    /**
     * Write a string value
     *
     * @param  string $value
     * @return void
     */
    protected function _writeString($value)
    {
        if ( ($id=$this->_searchMomo($value)) !== false ) {
            $this->_writeGet($id);
            return;
        }

        if ($this->_binary) {
            $n = strlen($value);
            if ($n <= 0xff) {
                // self.write(SHORT_BINSTRING + chr(n) + obj)
                $this->_pickle .= self::OP_SHORT_BINSTRING . chr($n) . $value;
            } else {
                // self.write(BINSTRING + pack("<i", n) + obj)
                $binLen = pack('l', $n);
                if (self::$_isLittleEndian === false) {
                    $binLen = strrev($binLen);
                }
                $this->_pickle .= self::OP_BINSTRING . $binLen . $value;
            }
        } else {
            $this->_pickle .= self::OP_STRING . $this->_quoteString($value) . "\r\n";
        }

        $this->_momorize($value);
    }

    /**
     * Write an associative array value as dictionary
     *
     * @param  array $value
     * @return void
     */
    protected function _writeArrayDict(array $value)
    {
        if (($id=$this->_searchMomo($value)) !== false) {
            $this->_writeGet($id);;
            return;
        }

        $this->_pickle .= self::OP_MARK . self::OP_DICT;
        $this->_momorize($value);

        foreach ($value as $k => $v) {
            $this->_pickle .= $this->_write($k)
                            . $this->_write($v)
                            . self::OP_SETITEM;
        }
    }

    /**
     * Write a simple array value as list
     *
     * @param  array $value
     * @return void
     */
    protected function _writeArrayList(array $value)
    {
        if (($id = $this->_searchMomo($value)) !== false) {
            $this->_writeGet($id);
            return;
        }

        $this->_pickle .= self::OP_MARK . self::OP_LIST;
        $this->_momorize($value);

        foreach ($value as $k => $v) {
            $this->_pickle .= $this->_write($v) . self::OP_APPEND;
        }
    }

    /**
     * Write an object as an dictionary
     *
     * @param  object $value
     * @return void
     */
    protected function _writeObject($value)
    {
        // can't serialize php objects to python objects yet
        $this->_writeArrayDict(get_object_vars($value));
    }

    /**
     * Write stop
     *
     * @return void
     */
    protected function _writeStop()
    {
        $this->_pickle .= self::OP_STOP;
    }

    /* serialize helper */

    /**
     * Add a value to the memo and write the id
     *
     * @param mixed $value
     * @return void
     */
    protected function _momorize($value)
    {
        $id = count($this->_memo);
        $this->_memo[$id] = $value;
        $this->_writePut($id);
    }

    /**
     * Search a value in the meno and return  the id
     *
     * @param  mixed $value
     * @return int|false The id or false
     */
    protected function _searchMomo($value)
    {
        return array_search($value, $this->_memo, true);
    }

    /**
     * Is an array associative?
     *
     * @param  array $value
     * @return boolean
     */
    protected function _isArrayAssoc(array $value)
    {
        return array_diff_key($value, array_keys(array_keys($value)));
    }

    /**
     * Quote/Escape a string
     *
     * @param  string $str
     * @return string quoted string
     */
    protected function _quoteString($str)
    {
        $quoteArr = self::$_quoteString;

        if (($cntSingleQuote = substr_count($str, "'"))
            && ($cntDoubleQuote = substr_count($str, '"'))
            && ($cntSingleQuote < $cntDoubleQuote)
        ) {
            $quoteArr['"'] = '\\"';
            $enclosure     = '"';
        } else {
            $quoteArr["'"] = "\\'";
            $enclosure     = "'";
        }

        return $enclosure . strtr($str, $quoteArr) . $enclosure;
    }

    /* unserialize */

    /**
     * Unserialize from Python Pickle format to PHP
     *
     * @param  string $pickle
     * @param  array $opts
     * @return mixed
     * @throws Zend_Serializer_Exception on invalid Pickle string
     */
    public function unserialize($pickle, array $opts = array())
    {
        // init process vars
        $this->_pos       = 0;
        $this->_pickle    = $pickle;
        $this->_pickleLen = strlen($this->_pickle);
        $this->_memo      = array();
        $this->_stack     = array();

        // read pickle string
        while (($op=$this->_read(1)) !== self::OP_STOP) {
            $this->_load($op);
        }

        if (!count($this->_stack)) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('No data found');
        }

        $ret = array_pop($this->_stack);

        // clear process vars
        $this->_pos       = 0;
        $this->_pickle    = '';
        $this->_pickleLen = 0;
        $this->_memo      = array();
        $this->_stack     = array();

        return $ret;
    }

    /**
     * Load a pickle opcode
     *
     * @param  string $op
     * @return void
     * @throws Zend_Serializer_Exception on invalid opcode
     */
    protected function _load($op)
    {
        switch ($op) {
            case self::OP_PUT:
                $this->_loadPut();
                break;
            case self::OP_BINPUT:
                $this->_loadBinPut();
                break;
            case self::OP_LONG_BINPUT:
                $this->_loadLongBinPut();
                break;
            case self::OP_GET:
                $this->_loadGet();
                break;
            case self::OP_BINGET:
                $this->_loadBinGet();
                break;
            case self::OP_LONG_BINGET:
                $this->_loadLongBinGet();
                break;
            case self::OP_NONE:
                $this->_loadNone();
                break;
            case self::OP_NEWTRUE:
                $this->_loadNewTrue();
                break;
            case self::OP_NEWFALSE:
                $this->_loadNewFalse();
                break;
            case self::OP_INT:
                $this->_loadInt();
                break;
            case self::OP_BININT:
                $this->_loadBinInt();
                break;
            case self::OP_BININT1:
                $this->_loadBinInt1();
                break;
            case self::OP_BININT2:
                $this->_loadBinInt2();
                break;
            case self::OP_LONG:
                $this->_loadLong();
                break;
            case self::OP_LONG1:
                $this->_loadLong1();
                break;
            case self::OP_LONG4:
                $this->_loadLong4();
                break;
            case self::OP_FLOAT:
                $this->_loadFloat();
                break;
            case self::OP_BINFLOAT:
                $this->_loadBinFloat();
                break;
            case self::OP_STRING:
                $this->_loadString();
                break;
            case self::OP_BINSTRING:
                $this->_loadBinString();
                break;
            case self::OP_SHORT_BINSTRING:
                $this->_loadShortBinString();
                break;
            case self::OP_BINBYTES:
                $this->_loadBinBytes();
                break;
            case self::OP_SHORT_BINBYTES:
                $this->_loadShortBinBytes();
                break;
            case self::OP_UNICODE:
                $this->_loadUnicode();
                break;
            case self::OP_BINUNICODE:
                $this->_loadBinUnicode();
                break;
            case self::OP_MARK:
                $this->_loadMark();
                break;
            case self::OP_LIST:
                $this->_loadList();
                break;
            case self::OP_EMPTY_LIST:
                $this->_loadEmptyList();
                break;
            case self::OP_APPEND:
                $this->_loadAppend();
                break;
            case self::OP_APPENDS:
                $this->_loadAppends();
                break;
            case self::OP_DICT:
                $this->_loadDict();
                break;
            case self::OP_EMPTY_DICT:
                $this->_loadEmptyDict();
                break;
            case self::OP_SETITEM:
                $this->_loadSetItem();
                break;
            case self::OP_SETITEMS:
                $this->_loadSetItems();
                break;
            case self::OP_TUPLE:
                $this->_loadTuple();
                break;
            case self::OP_TUPLE1:
                $this->_loadTuple1();
                break;
            case self::OP_TUPLE2:
                $this->_loadTuple2();
                break;
            case self::OP_TUPLE3:
                $this->_loadTuple3();
                break;
            case self::OP_PROTO:
                $this->_loadProto();
                break;
            default:
                #require_once 'Zend/Serializer/Exception.php';
                throw new Zend_Serializer_Exception('Invalid or unknown opcode "'.$op.'"');
        }
    }

    /**
     * Load a PUT opcode
     *
     * @return void
     * @throws Zend_Serializer_Exception on missing stack
     */
    protected function _loadPut()
    {
        $id = (int)$this->_readline();

        $lastStack = count($this->_stack)-1;
        if (!isset($this->_stack[$lastStack])) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('No stack exist');
        }
        $this->_memo[$id] = & $this->_stack[$lastStack];
    }

    /**
     * Load a binary PUT
     *
     * @return void
     * @throws Zend_Serializer_Exception on missing stack
     */
    protected function _loadBinPut()
    {
        $id = ord($this->_read(1));

        $lastStack = count($this->_stack)-1;
        if (!isset($this->_stack[$lastStack])) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('No stack exist');
        }
        $this->_memo[$id] = & $this->_stack[$lastStack];
    }

    /**
     * Load a long binary PUT
     *
     * @return void
     * @throws Zend_Serializer_Exception on missing stack
     */
    protected function _loadLongBinPut()
    {
        $bin = $this->_read(4);
        if (self::$_isLittleEndian === false) {
            $bin = strrev($bin);
        }
        list(, $id) = unpack('l', $bin);

        $lastStack = count($this->_stack)-1;
        if (!isset($this->_stack[$lastStack])) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('No stack exist');
        }
        $this->_memo[$id] = & $this->_stack[$lastStack];
    }

    /**
     * Load a GET operation
     *
     * @return void
     * @throws Zend_Serializer_Exception on missing GET identifier
     */
    protected function _loadGet()
    {
        $id = (int)$this->_readline();

        if (!array_key_exists($id, $this->_memo)) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('Get id "' . $id . '" not found in momo');
        }
        $this->_stack[] = & $this->_memo[$id];
    }

    /**
     * Load a binary GET operation
     *
     * @return void
     * @throws Zend_Serializer_Exception on missing GET identifier
     */
    protected function _loadBinGet()
    {
        $id = ord($this->_read(1));

        if (!array_key_exists($id, $this->_memo)) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('Get id "' . $id . '" not found in momo');
        }
        $this->_stack[] = & $this->_memo[$id];
    }

    /**
     * Load a long binary GET operation
     *
     * @return void
     * @throws Zend_Serializer_Exception on missing GET identifier
     */
    protected function _loadLongBinGet()
    {
        $bin = $this->_read(4);
        if (self::$_isLittleEndian === false) {
            $bin = strrev($bin);
        }
        list(, $id) = unpack('l', $bin);

        if (!array_key_exists($id, $this->_memo)) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('Get id "' . $id . '" not found in momo');
        }
        $this->_stack[] = & $this->_memo[$id];
    }

    /**
     * Load a NONE operator
     *
     * @return void
     */
    protected function _loadNone()
    {
        $this->_stack[] = null;
    }

    /**
     * Load a boolean TRUE operator
     *
     * @return void
     */
    protected function _loadNewTrue()
    {
        $this->_stack[] = true;
    }

    /**
     * Load a boolean FALSE operator
     *
     * @return void
     */
    protected function _loadNewFalse()
    {
        $this->_stack[] = false;
    }

    /**
     * Load an integer operator
     *
     * @return void
     */
    protected function _loadInt()
    {
        $line = $this->_readline();
        if ($line === '01') {
            $this->_stack[] = true;
        } elseif ($line === '00') {
            $this->_stack[] = false;
        } else {
            $this->_stack[] = (int)$line;
        }
    }

    /**
     * Load a binary integer operator
     *
     * @return void
     */
    protected function _loadBinInt()
    {
        $bin = $this->_read(4);
        if (self::$_isLittleEndian === false) {
            $bin = strrev($bin);
        }
        list(, $int)    = unpack('l', $bin);
        $this->_stack[] = $int;
    }

    /**
     * Load the first byte of a binary integer
     *
     * @return void
     */
    protected function _loadBinInt1()
    {
        $this->_stack[] = ord($this->_read(1));
    }

    /**
     * Load the second byte of a binary integer
     *
     * @return void
     */
    protected function _loadBinInt2()
    {
        $bin = $this->_read(2);
        list(, $int)    = unpack('v', $bin);
        $this->_stack[] = $int;
    }

    /**
     * Load a long (float) operator
     *
     * @return void
     */
    protected function _loadLong()
    {
        $data = rtrim($this->_readline(), 'L');
        if ($data === '') {
            $this->_stack[] = 0;
        } else {
            $this->_stack[] = $data;
        }
    }

    /**
     * Load a one byte long integer
     *
     * @return void
     */
    protected function _loadLong1()
    {
        $n    = ord($this->_read(1));
        $data = $this->_read($n);
        $this->_stack[] = $this->_decodeBinLong($data);
    }

    /**
     * Load a 4 byte long integer
     *
     * @return void
     */
    protected function _loadLong4()
    {
        $nBin = $this->_read(4);
        if (self::$_isLittleEndian === false) {
            $nBin = strrev($$nBin);
        }
        list(, $n) = unpack('l', $nBin);
        $data = $this->_read($n);

        $this->_stack[] = $this->_decodeBinLong($data);
    }

    /**
     * Load a float value
     *
     * @return void
     */
    protected function _loadFloat()
    {
        $float = (float)$this->_readline();
        $this->_stack[] = $float;
    }

    /**
     * Load a binary float value
     *
     * @return void
     */
    protected function _loadBinFloat()
    {
        $bin = $this->_read(8);
        if (self::$_isLittleEndian === true) {
            $bin = strrev($bin);
        }
        list(, $float)  = unpack('d', $bin);
        $this->_stack[] = $float;
    }

    /**
     * Load a string
     *
     * @return void
     */
    protected function _loadString()
    {
        $this->_stack[] = $this->_unquoteString((string)$this->_readline());
    }

    /**
     * Load a binary string
     *
     * @return void
     */
    protected function _loadBinString()
    {
        $bin = $this->_read(4);
        if (!self::$_isLittleEndian) {
            $bin = strrev($bin);
        }
        list(, $len)    = unpack('l', $bin);
        $this->_stack[] = (string)$this->_read($len);
    }

    /**
     * Load a short binary string
     *
     * @return void
     */
    protected function _loadShortBinString()
    {
        $len            = ord($this->_read(1));
        $this->_stack[] = (string)$this->_read($len);
    }

    /**
     * Load arbitrary binary bytes
     *
     * @return void
     */
    protected function _loadBinBytes()
    {
        // read byte length
        $nBin = $this->_read(4);
        if (self::$_isLittleEndian === false) {
            $nBin = strrev($$nBin);
        }
        list(, $n)      = unpack('l', $nBin);
        $this->_stack[] = $this->_read($n);
    }

    /**
     * Load a single binary byte
     *
     * @return void
     */
    protected function _loadShortBinBytes()
    {
        $n              = ord($this->_read(1));
        $this->_stack[] = $this->_read($n);
    }

    /**
     * Load a unicode string
     *
     * @return void
     */
    protected function _loadUnicode()
    {
        $data    = $this->_readline();
        $pattern = '/\\\\u([a-fA-F0-9]{4})/u'; // \uXXXX
        $data    = preg_replace_callback($pattern, array($this, '_convertMatchingUnicodeSequence2Utf8'), $data);

        if (self::$_isPhp6) {
            $data = unicode_decode($data, 'UTF-8');
        }

        $this->_stack[] = $data;
    }

    /**
     * Convert a unicode sequence to UTF-8
     *
     * @param  array $match
     * @return string
     */
    protected function _convertMatchingUnicodeSequence2Utf8(array $match)
    {
        return $this->_hex2Utf8($match[1]);
    }

    /**
     * Convert a hex string to a UTF-8 string
     *
     * @param  string $sequence
     * @return string
     * @throws Zend_Serializer_Exception on unmatched unicode sequence
     */
    protected function _hex2Utf8($hex)
    {
        $uniCode = hexdec($hex);

        if ($uniCode < 0x80) { // 1Byte
            $utf8Char = chr($uniCode);

        } elseif ($uniCode < 0x800) { // 2Byte
            $utf8Char = chr(0xC0 | $uniCode >> 6)
                      . chr(0x80 | $uniCode & 0x3F);

        } elseif ($uniCode < 0x10000) { // 3Byte
            $utf8Char = chr(0xE0 | $uniCode >> 12)
                      . chr(0x80 | $uniCode >> 6 & 0x3F)
                      . chr(0x80 | $uniCode & 0x3F);

        } elseif ($uniCode < 0x110000) { // 4Byte
            $utf8Char  = chr(0xF0 | $uniCode >> 18)
                       . chr(0x80 | $uniCode >> 12 & 0x3F)
                       . chr(0x80 | $uniCode >> 6 & 0x3F)
                       . chr(0x80 | $uniCode & 0x3F);
        } else {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('Unsupported unicode character found "' . dechex($uniCode) . '"');
        }

        return $utf8Char;
    }

    /**
     * Load binary unicode sequence
     *
     * @return void
     */
    protected function _loadBinUnicode()
    {
        // read byte length
        $n = $this->_read(4);
        if (self::$_isLittleEndian === false) {
            $n = strrev($n);
        }
        list(, $n) = unpack('l', $n);
        $data      = $this->_read($n);

        if (self::$_isPhp6) {
            $data = unicode_decode($data, 'UTF-8');
        }

        $this->_stack[] = $data;
    }

    /**
     * Load a marker sequence
     *
     * @return void
     */
    protected function _loadMark()
    {
        $this->_stack[] = $this->_marker;
    }

    /**
     * Load an array (list)
     *
     * @return void
     */
    protected function _loadList()
    {
        $k = $this->_lastMarker();
        $this->_stack[$k] = array();

        // remove all elements after marker
        $max = count($this->_stack);
        for ($i = $k+1, $max; $i < $max; $i++) {
            unset($this->_stack[$i]);
        }
    }

    /**
     * Load an append (to list) sequence
     *
     * @return void
     */
    protected function _loadAppend()
    {
        $value  =  array_pop($this->_stack);
        $list   =& $this->_stack[count($this->_stack)-1];
        $list[] =  $value;
    }

    /**
     * Load an empty list sequence
     *
     * @return void
     */
    protected function _loadEmptyList()
    {
        $this->_stack[] = array();
    }

    /**
     * Load multiple append (to list) sequences at once
     *
     * @return void
     */
    protected function _loadAppends()
    {
        $k    =  $this->_lastMarker();
        $list =& $this->_stack[$k - 1];
        $max  =  count($this->_stack);
        for ($i = $k + 1; $i < $max; $i++) {
            $list[] = $this->_stack[$i];
            unset($this->_stack[$i]);
        }
        unset($this->_stack[$k]);
    }

    /**
     * Load an associative array (Python dictionary)
     *
     * @return void
     */
    protected function _loadDict()
    {
        $k = $this->_lastMarker();
        $this->_stack[$k] = array();

        // remove all elements after marker
        $max = count($this->_stack);
        for($i = $k + 1; $i < $max; $i++) {
            unset($this->_stack[$i]);
        }
    }

    /**
     * Load an item from a set
     *
     * @return void
     */
    protected function _loadSetItem()
    {
        $value =  array_pop($this->_stack);
        $key   =  array_pop($this->_stack);
        $dict  =& $this->_stack[count($this->_stack) - 1];
        $dict[$key] = $value;
    }

    /**
     * Load an empty dictionary
     *
     * @return void
     */
    protected function _loadEmptyDict()
    {
        $this->_stack[] = array();
    }

    /**
     * Load set items
     *
     * @return void
     */
    protected function _loadSetItems()
    {
        $k    =  $this->_lastMarker();
        $dict =& $this->_stack[$k - 1];
        $max  =  count($this->_stack);
        for ($i = $k + 1; $i < $max; $i += 2) {
            $key        = $this->_stack[$i];
            $value      = $this->_stack[$i + 1];
            $dict[$key] = $value;
            unset($this->_stack[$i], $this->_stack[$i+1]);
        }
        unset($this->_stack[$k]);
    }

    /**
     * Load a tuple
     *
     * @return void
     */
    protected function _loadTuple()
    {
        $k                =  $this->_lastMarker();
        $this->_stack[$k] =  array();
        $tuple            =& $this->_stack[$k];
        $max              =  count($this->_stack);
        for($i = $k + 1; $i < $max; $i++) {
            $tuple[] = $this->_stack[$i];
            unset($this->_stack[$i]);
        }
    }

    /**
     * Load single item tuple
     *
     * @return void
     */
    protected function _loadTuple1()
    {
        $value1 = array_pop($this->_stack);
        $this->_stack[] = array($value1);
    }

    /**
     * Load two item tuple
     *
     * @return void
     */
    protected function _loadTuple2()
    {
        $value2 = array_pop($this->_stack);
        $value1 = array_pop($this->_stack);
        $this->_stack[] = array($value1, $value2);
    }

    /**
     * Load three item tuple
     *
     * @return void
     */
    protected function _loadTuple3() {
        $value3 = array_pop($this->_stack);
        $value2 = array_pop($this->_stack);
        $value1 = array_pop($this->_stack);
        $this->_stack[] = array($value1, $value2, $value3);
    }

    /**
     * Load a proto value
     *
     * @return void
     * @throws Zend_Serializer_Exception if Pickle version does not support this feature
     */
    protected function _loadProto()
    {
        $proto = ord($this->_read(1));
        if ($proto < 2 || $proto > 3) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('Invalid protocol version detected');
        }
        $this->_protocol = $proto;
    }

    /* unserialize helper */

    /**
     * Read a segment of the pickle
     *
     * @param  mixed $len
     * @return string
     * @throws Zend_Serializer_Exception if position matches end of data
     */
    protected function _read($len)
    {
        if (($this->_pos + $len) > $this->_pickleLen) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('End of data');
        }

        $this->_pos+= $len;
        return substr($this->_pickle, ($this->_pos - $len), $len);
    }

    /**
     * Read a line of the pickle at once
     *
     * @return string
     * @throws Zend_Serializer_Exception if no EOL character found
     */
    protected function _readline()
    {
        $eolLen = 2;
        $eolPos = strpos($this->_pickle, "\r\n", $this->_pos);
        if ($eolPos === false) {
            $eolPos = strpos($this->_pickle, "\n", $this->_pos);
            $eolLen = 1;
        }

        if ($eolPos === false) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('No new line found');
        }
        $ret        = substr($this->_pickle, $this->_pos, $eolPos-$this->_pos);
        $this->_pos = $eolPos + $eolLen;

        return $ret;
    }

    /**
     * Unquote/Unescape a quoted string
     *
     * @param  string $str quoted string
     * @return string unquoted string
     */
    protected function _unquoteString($str)
    {
        $quoteArr = array_flip(self::$_quoteString);

        if ($str[0] == '"') {
            $quoteArr['\\"'] = '"';
        } else {
            $quoteArr["\\'"] = "'";
        }

        return strtr(substr(trim($str), 1, -1), $quoteArr);
    }

    /**
     * Return last marker position in stack
     *
     * @return int
     */
    protected function _lastMarker()
    {
        for ($k = count($this->_stack)-1; $k >= 0; $k -= 1) {
            if ($this->_stack[$k] === $this->_marker) {
                break;
            }
        }
        return $k;
    }

    /**
     * Decode a binary long sequence
     *
     * @param  string $data
     * @return int|float|string
     */
    protected function _decodeBinLong($data)
    {
        $nbytes = strlen($data);

        if ($nbytes == 0) {
            return 0;
        }

        $long = 0;

        if ($nbytes > 7) {
            if (!extension_loaded('bcmath')) {
                return INF;
            }

            for ($i=0; $i<$nbytes; $i++) {
                $long = bcadd($long, bcmul(ord($data[$i]), bcpow(256, $i, 0)));
            }
            if (0x80 <= ord($data[$nbytes-1])) {
                $long = bcsub($long, bcpow(2, $nbytes * 8));
            }

        } else {
            for ($i=0; $i<$nbytes; $i++) {
                $long+= ord($data[$i]) * pow(256, $i);
            }
            if (0x80 <= ord($data[$nbytes-1])) {
                $long-= pow(2, $nbytes * 8);
                // $long-= 1 << ($nbytes * 8);
            }
        }

        return $long;
    }
}
