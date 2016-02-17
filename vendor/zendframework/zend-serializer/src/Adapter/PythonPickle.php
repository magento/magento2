<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Serializer\Adapter;

use stdClass;
use Traversable;
use Zend\Math\BigInteger;
use Zend\Serializer\Exception;
use Zend\Stdlib\ArrayUtils;

/**
 * @link       http://www.python.org
 * @see        Phython3.1/Lib/pickle.py
 * @see        Phython3.1/Modules/_pickle.c
 * @link       http://pickle-js.googlecode.com
 */
class PythonPickle extends AbstractAdapter
{
    /**
     * Pickle opcodes. See pickletools.py for extensive docs.
     * @link http://hg.python.org/cpython/file/2.7/Lib/pickletools.py
     * The listing here is in kind-of alphabetical order of 1-character pickle code.
     * pickletools groups them by purpose.
     */
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
     * Whether or not the system is little-endian
     *
     * @var bool
     */
    protected static $isLittleEndian = null;

    /**
     * @var array Strings representing quotes
     */
    protected static $quoteString = array(
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

    // process vars
    protected $protocol  = null;
    protected $memo      = array();
    protected $pickle    = '';
    protected $pickleLen = 0;
    protected $pos       = 0;
    protected $stack     = array();
    protected $marker    = null;

    /**
     * @var BigInteger\Adapter\AdapterInterface
     */
    protected $bigIntegerAdapter = null;

    /**
     * @var PythonPickleOptions
     */
    protected $options = null;

    /**
     * Constructor.
     *
     * @param  array|Traversable|PythonPickleOptions $options Optional
     */
    public function __construct($options = null)
    {
        // init
        if (static::$isLittleEndian === null) {
            static::$isLittleEndian = (pack('l', 1) === "\x01\x00\x00\x00");
        }

        $this->marker = new stdClass();

        parent::__construct($options);
    }

    /**
     * Set options
     *
     * @param  array|Traversable|PythonPickleOptions $options
     * @return PythonPickle
     */
    public function setOptions($options)
    {
        if (!$options instanceof PythonPickleOptions) {
            $options = new PythonPickleOptions($options);
        }

        $this->options = $options;
        return $this;
    }

    /**
     * Get options
     *
     * @return PythonPickleOptions
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = new PythonPickleOptions();
        }
        return $this->options;
    }

    /* serialize */

    /**
     * Serialize PHP to PythonPickle format
     *
     * @param  mixed $value
     * @return string
     */
    public function serialize($value)
    {
        $this->clearProcessVars();
        $this->protocol = $this->getOptions()->getProtocol();

        // write
        if ($this->protocol >= 2) {
            $this->writeProto($this->protocol);
        }
        $this->write($value);
        $this->writeStop();

        $pickle = $this->pickle;
        $this->clearProcessVars();

        return $pickle;
    }

    /**
     * Write a value
     *
     * @param  mixed $value
     * @throws Exception\RuntimeException on invalid or unrecognized value type
     */
    protected function write($value)
    {
        if ($value === null) {
            $this->writeNull();
        } elseif (is_bool($value)) {
            $this->writeBool($value);
        } elseif (is_int($value)) {
            $this->writeInt($value);
        } elseif (is_float($value)) {
            $this->writeFloat($value);
        } elseif (is_string($value)) {
            // TODO: write unicode / binary
            $this->writeString($value);
        } elseif (is_array($value)) {
            if (ArrayUtils::isList($value)) {
                $this->writeArrayList($value);
            } else {
                $this->writeArrayDict($value);
            }
        } elseif (is_object($value)) {
            $this->writeObject($value);
        } else {
            throw new Exception\RuntimeException(sprintf(
                'PHP-Type "%s" can not be serialized by %s',
                gettype($value),
                get_class($this)
            ));
        }
    }

    /**
     * Write pickle protocol
     *
     * @param int $protocol
     */
    protected function writeProto($protocol)
    {
        $this->pickle .= self::OP_PROTO . $protocol;
    }

    /**
     * Write a get
     *
     * @param  int $id Id of memo
     */
    protected function writeGet($id)
    {
        if ($this->protocol == 0) {
            $this->pickle .= self::OP_GET . $id . "\r\n";
        } elseif ($id <= 0xFF) {
            // BINGET + chr(i)
            $this->pickle .= self::OP_BINGET . chr($id);
        } else {
            // LONG_BINGET + pack("<i", i)
            $bin = pack('l', $id);
            if (static::$isLittleEndian === false) {
                $bin = strrev($bin);
            }
            $this->pickle .= self::OP_LONG_BINGET . $bin;
        }
    }

    /**
     * Write a put
     *
     * @param  int $id Id of memo
     */
    protected function writePut($id)
    {
        if ($this->protocol == 0) {
            $this->pickle .= self::OP_PUT . $id . "\r\n";
        } elseif ($id <= 0xff) {
            // BINPUT + chr(i)
            $this->pickle .= self::OP_BINPUT . chr($id);
        } else {
            // LONG_BINPUT + pack("<i", i)
            $bin = pack('l', $id);
            if (static::$isLittleEndian === false) {
                $bin = strrev($bin);
            }
            $this->pickle .= self::OP_LONG_BINPUT . $bin;
        }
    }

    /**
     * Write a null as None
     *
     */
    protected function writeNull()
    {
        $this->pickle .= self::OP_NONE;
    }

    /**
     * Write boolean value
     *
     * @param bool $value
     */
    protected function writeBool($value)
    {
        if ($this->protocol >= 2) {
            $this->pickle .= ($value === true) ? self::OP_NEWTRUE : self::OP_NEWFALSE;
        } else {
            $this->pickle .= self::OP_INT . (($value === true) ? '01' : '00') . "\r\n";
        }
    }

    /**
     * Write an integer value
     *
     * @param  int $value
     */
    protected function writeInt($value)
    {
        if ($this->protocol == 0) {
            $this->pickle .= self::OP_INT . $value . "\r\n";
            return;
        }

        if ($value >= 0) {
            if ($value <= 0xFF) {
                // self.write(BININT1 + chr(obj))
                $this->pickle .= self::OP_BININT1 . chr($value);
            } elseif ($value <= 0xFFFF) {
                // self.write("%c%c%c" % (BININT2, obj&0xff, obj>>8))
                $this->pickle .= self::OP_BININT2 . pack('v', $value);
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
            if (static::$isLittleEndian === false) {
                $bin = strrev($bin);
            }
            $this->pickle .= self::OP_BININT . $bin;
            return;
        }
    }

    /**
     * Write a float value
     *
     * @param  float $value
     */
    protected function writeFloat($value)
    {
        if ($this->protocol == 0) {
            $this->pickle .= self::OP_FLOAT . $value . "\r\n";
        } else {
            // self.write(BINFLOAT + pack('>d', obj))
            $bin = pack('d', $value);
            if (static::$isLittleEndian === true) {
                $bin = strrev($bin);
            }
            $this->pickle .= self::OP_BINFLOAT . $bin;
        }
    }

    /**
     * Write a string value
     *
     * @param  string $value
     */
    protected function writeString($value)
    {
        if (($id = $this->searchMemo($value)) !== false) {
            $this->writeGet($id);
            return;
        }

        if ($this->protocol == 0) {
            $this->pickle .= self::OP_STRING . $this->quoteString($value) . "\r\n";
        } else {
            $n = strlen($value);
            if ($n <= 0xFF) {
                // self.write(SHORT_BINSTRING + chr(n) + obj)
                $this->pickle .= self::OP_SHORT_BINSTRING . chr($n) . $value;
            } else {
                // self.write(BINSTRING + pack("<i", n) + obj)
                $binLen = pack('l', $n);
                if (static::$isLittleEndian === false) {
                    $binLen = strrev($binLen);
                }
                $this->pickle .= self::OP_BINSTRING . $binLen . $value;
            }
        }

        $this->memorize($value);
    }

    /**
     * Write an associative array value as dictionary
     *
     * @param  array|Traversable $value
     */
    protected function writeArrayDict($value)
    {
        if (($id = $this->searchMemo($value)) !== false) {
            $this->writeGet($id);
            return;
        }

        $this->pickle .= self::OP_MARK . self::OP_DICT;
        $this->memorize($value);

        foreach ($value as $k => $v) {
            $this->write($k);
            $this->write($v);
            $this->pickle .= self::OP_SETITEM;
        }
    }

    /**
     * Write a simple array value as list
     *
     * @param  array $value
     */
    protected function writeArrayList(array $value)
    {
        if (($id = $this->searchMemo($value)) !== false) {
            $this->writeGet($id);
            return;
        }

        $this->pickle .= self::OP_MARK . self::OP_LIST;
        $this->memorize($value);

        foreach ($value as $v) {
            $this->write($v);
            $this->pickle .= self::OP_APPEND;
        }
    }

    /**
     * Write an object as a dictionary
     *
     * @param  object $value
     */
    protected function writeObject($value)
    {
        // The main differences between a SplFixedArray and a normal PHP array is
        // that the SplFixedArray is of fixed length and allows only integers
        // within the range as indexes.
        if ($value instanceof \SplFixedArray) {
            $this->writeArrayList($value->toArray());

        // Use the object method toArray if available
        } elseif (method_exists($value, 'toArray')) {
            $this->writeArrayDict($value->toArray());

        // If the object is an iterator simply iterate it
        // and convert it to a dictionary
        } elseif ($value instanceof Traversable) {
            $this->writeArrayDict($value);

        // other objects are simply converted by using its properties
        } else {
            $this->writeArrayDict(get_object_vars($value));
        }
    }

    /**
     * Write stop
     */
    protected function writeStop()
    {
        $this->pickle .= self::OP_STOP;
    }

    /* serialize helper */

    /**
     * Add a value to the memo and write the id
     *
     * @param mixed $value
     */
    protected function memorize($value)
    {
        $id = count($this->memo);
        $this->memo[$id] = $value;
        $this->writePut($id);
    }

    /**
     * Search a value in the memo and return  the id
     *
     * @param  mixed $value
     * @return int|bool The id or false
     */
    protected function searchMemo($value)
    {
        return array_search($value, $this->memo, true);
    }

    /**
     * Quote/Escape a string
     *
     * @param  string $str
     * @return string quoted string
     */
    protected function quoteString($str)
    {
        $quoteArr = static::$quoteString;

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
     * @return mixed
     * @throws Exception\RuntimeException on invalid Pickle string
     */
    public function unserialize($pickle)
    {
        // init process vars
        $this->clearProcessVars();
        $this->pickle    = $pickle;
        $this->pickleLen = strlen($this->pickle);

        // read pickle string
        while (($op = $this->read(1)) !== self::OP_STOP) {
            $this->load($op);
        }

        if (!count($this->stack)) {
            throw new Exception\RuntimeException('No data found');
        }

        $ret = array_pop($this->stack);

        // clear process vars
        $this->clearProcessVars();

        return $ret;
    }

    /**
     * Clear temp variables needed for processing
     */
    protected function clearProcessVars()
    {
        $this->pos       = 0;
        $this->pickle    = '';
        $this->pickleLen = 0;
        $this->memo      = array();
        $this->stack     = array();
    }

    /**
     * Load a pickle opcode
     *
     * @param  string $op
     * @throws Exception\RuntimeException on invalid opcode
     */
    protected function load($op)
    {
        switch ($op) {
            case self::OP_PUT:
                $this->loadPut();
                break;
            case self::OP_BINPUT:
                $this->loadBinPut();
                break;
            case self::OP_LONG_BINPUT:
                $this->loadLongBinPut();
                break;
            case self::OP_GET:
                $this->loadGet();
                break;
            case self::OP_BINGET:
                $this->loadBinGet();
                break;
            case self::OP_LONG_BINGET:
                $this->loadLongBinGet();
                break;
            case self::OP_NONE:
                $this->loadNone();
                break;
            case self::OP_NEWTRUE:
                $this->loadNewTrue();
                break;
            case self::OP_NEWFALSE:
                $this->loadNewFalse();
                break;
            case self::OP_INT:
                $this->loadInt();
                break;
            case self::OP_BININT:
                $this->loadBinInt();
                break;
            case self::OP_BININT1:
                $this->loadBinInt1();
                break;
            case self::OP_BININT2:
                $this->loadBinInt2();
                break;
            case self::OP_LONG:
                $this->loadLong();
                break;
            case self::OP_LONG1:
                $this->loadLong1();
                break;
            case self::OP_LONG4:
                $this->loadLong4();
                break;
            case self::OP_FLOAT:
                $this->loadFloat();
                break;
            case self::OP_BINFLOAT:
                $this->loadBinFloat();
                break;
            case self::OP_STRING:
                $this->loadString();
                break;
            case self::OP_BINSTRING:
                $this->loadBinString();
                break;
            case self::OP_SHORT_BINSTRING:
                $this->loadShortBinString();
                break;
            case self::OP_BINBYTES:
                $this->loadBinBytes();
                break;
            case self::OP_SHORT_BINBYTES:
                $this->loadShortBinBytes();
                break;
            case self::OP_UNICODE:
                $this->loadUnicode();
                break;
            case self::OP_BINUNICODE:
                $this->loadBinUnicode();
                break;
            case self::OP_MARK:
                $this->loadMark();
                break;
            case self::OP_LIST:
                $this->loadList();
                break;
            case self::OP_EMPTY_LIST:
                $this->loadEmptyList();
                break;
            case self::OP_APPEND:
                $this->loadAppend();
                break;
            case self::OP_APPENDS:
                $this->loadAppends();
                break;
            case self::OP_DICT:
                $this->loadDict();
                break;
            case self::OP_EMPTY_DICT:
                $this->_loadEmptyDict();
                break;
            case self::OP_SETITEM:
                $this->loadSetItem();
                break;
            case self::OP_SETITEMS:
                $this->loadSetItems();
                break;
            case self::OP_TUPLE:
                $this->loadTuple();
                break;
            case self::OP_TUPLE1:
                $this->loadTuple1();
                break;
            case self::OP_TUPLE2:
                $this->loadTuple2();
                break;
            case self::OP_TUPLE3:
                $this->loadTuple3();
                break;
            case self::OP_PROTO:
                $this->loadProto();
                break;
            default:
                throw new Exception\RuntimeException("Invalid or unknown opcode '{$op}'");
        }
    }

    /**
     * Load a PUT opcode
     *
     * @throws Exception\RuntimeException on missing stack
     */
    protected function loadPut()
    {
        $id = (int) $this->readline();

        $lastStack = count($this->stack) - 1;
        if (!isset($this->stack[$lastStack])) {
            throw new Exception\RuntimeException('No stack exist');
        }
        $this->memo[$id] =& $this->stack[$lastStack];
    }

    /**
     * Load a binary PUT
     *
     * @throws Exception\RuntimeException on missing stack
     */
    protected function loadBinPut()
    {
        $id = ord($this->read(1));

        $lastStack = count($this->stack)-1;
        if (!isset($this->stack[$lastStack])) {
            throw new Exception\RuntimeException('No stack exist');
        }
        $this->memo[$id] =& $this->stack[$lastStack];
    }

    /**
     * Load a long binary PUT
     *
     * @throws Exception\RuntimeException on missing stack
     */
    protected function loadLongBinPut()
    {
        $bin = $this->read(4);
        if (static::$isLittleEndian === false) {
            $bin = strrev($bin);
        }
        list(, $id) = unpack('l', $bin);

        $lastStack = count($this->stack)-1;
        if (!isset($this->stack[$lastStack])) {
            throw new Exception\RuntimeException('No stack exist');
        }
        $this->memo[$id] =& $this->stack[$lastStack];
    }

    /**
     * Load a GET operation
     *
     * @throws Exception\RuntimeException on missing GET identifier
     */
    protected function loadGet()
    {
        $id = (int) $this->readline();

        if (!array_key_exists($id, $this->memo)) {
            throw new Exception\RuntimeException('Get id "' . $id . '" not found in memo');
        }
        $this->stack[] =& $this->memo[$id];
    }

    /**
     * Load a binary GET operation
     *
     * @throws Exception\RuntimeException on missing GET identifier
     */
    protected function loadBinGet()
    {
        $id = ord($this->read(1));

        if (!array_key_exists($id, $this->memo)) {
            throw new Exception\RuntimeException('Get id "' . $id . '" not found in memo');
        }
        $this->stack[] =& $this->memo[$id];
    }

    /**
     * Load a long binary GET operation
     *
     * @throws Exception\RuntimeException on missing GET identifier
     */
    protected function loadLongBinGet()
    {
        $bin = $this->read(4);
        if (static::$isLittleEndian === false) {
            $bin = strrev($bin);
        }
        list(, $id) = unpack('l', $bin);

        if (!array_key_exists($id, $this->memo)) {
            throw new Exception\RuntimeException('Get id "' . $id . '" not found in memo');
        }
        $this->stack[] =& $this->memo[$id];
    }

    /**
     * Load a NONE operator
     */
    protected function loadNone()
    {
        $this->stack[] = null;
    }

    /**
     * Load a boolean TRUE operator
     */
    protected function loadNewTrue()
    {
        $this->stack[] = true;
    }

    /**
     * Load a boolean FALSE operator
     */
    protected function loadNewFalse()
    {
        $this->stack[] = false;
    }

    /**
     * Load an integer operator
     */
    protected function loadInt()
    {
        $line = $this->readline();
        if ($line === '01') {
            $this->stack[] = true;
        } elseif ($line === '00') {
            $this->stack[] = false;
        } else {
            $this->stack[] = (int) $line;
        }
    }

    /**
     * Load a binary integer operator
     */
    protected function loadBinInt()
    {
        $bin = $this->read(4);
        if (static::$isLittleEndian === false) {
            $bin = strrev($bin);
        }
        list(, $int)   = unpack('l', $bin);
        $this->stack[] = $int;
    }

    /**
     * Load the first byte of a binary integer
     */
    protected function loadBinInt1()
    {
        $this->stack[] = ord($this->read(1));
    }

    /**
     * Load the second byte of a binary integer
     */
    protected function loadBinInt2()
    {
        $bin = $this->read(2);
        list(, $int)   = unpack('v', $bin);
        $this->stack[] = $int;
    }

    /**
     * Load a long (float) operator
     */
    protected function loadLong()
    {
        $data = rtrim($this->readline(), 'L');
        if ($data === '') {
            $this->stack[] = 0;
        } else {
            $this->stack[] = $data;
        }
    }

    /**
     * Load a one byte long integer
     */
    protected function loadLong1()
    {
        $n    = ord($this->read(1));
        $data = $this->read($n);
        $this->stack[] = $this->decodeBinLong($data);
    }

    /**
     * Load a 4 byte long integer
     *
     */
    protected function loadLong4()
    {
        $nBin = $this->read(4);
        if (static::$isLittleEndian === false) {
            $nBin = strrev($$nBin);
        }
        list(, $n) = unpack('l', $nBin);
        $data = $this->read($n);

        $this->stack[] = $this->decodeBinLong($data);
    }

    /**
     * Load a float value
     *
     */
    protected function loadFloat()
    {
        $float = (float) $this->readline();
        $this->stack[] = $float;
    }

    /**
     * Load a binary float value
     *
     */
    protected function loadBinFloat()
    {
        $bin = $this->read(8);
        if (static::$isLittleEndian === true) {
            $bin = strrev($bin);
        }
        list(, $float) = unpack('d', $bin);
        $this->stack[] = $float;
    }

    /**
     * Load a string
     *
     */
    protected function loadString()
    {
        $this->stack[] = $this->unquoteString((string) $this->readline());
    }

    /**
     * Load a binary string
     *
     */
    protected function loadBinString()
    {
        $bin = $this->read(4);
        if (!static::$isLittleEndian) {
            $bin = strrev($bin);
        }
        list(, $len)   = unpack('l', $bin);
        $this->stack[] = (string) $this->read($len);
    }

    /**
     * Load a short binary string
     *
     */
    protected function loadShortBinString()
    {
        $len           = ord($this->read(1));
        $this->stack[] = (string) $this->read($len);
    }

    /**
     * Load arbitrary binary bytes
     */
    protected function loadBinBytes()
    {
        // read byte length
        $nBin = $this->read(4);
        if (static::$isLittleEndian === false) {
            $nBin = strrev($$nBin);
        }
        list(, $n)     = unpack('l', $nBin);
        $this->stack[] = $this->read($n);
    }

    /**
     * Load a single binary byte
     */
    protected function loadShortBinBytes()
    {
        $n             = ord($this->read(1));
        $this->stack[] = $this->read($n);
    }

    /**
     * Load a unicode string
     */
    protected function loadUnicode()
    {
        $data    = $this->readline();
        $pattern = '/\\\\u([a-fA-F0-9]{4})/u'; // \uXXXX
        $data    = preg_replace_callback($pattern, array($this, '_convertMatchingUnicodeSequence2Utf8'), $data);

        $this->stack[] = $data;
    }

    /**
     * Convert a unicode sequence to UTF-8
     *
     * @param  array $match
     * @return string
     */
    protected function _convertMatchingUnicodeSequence2Utf8(array $match)
    {
        return $this->hex2Utf8($match[1]);
    }

    /**
     * Convert a hex string to a UTF-8 string
     *
     * @param  string $hex
     * @return string
     * @throws Exception\RuntimeException on unmatched unicode sequence
     */
    protected function hex2Utf8($hex)
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
            throw new Exception\RuntimeException(
                sprintf('Unsupported unicode character found "%s"', dechex($uniCode))
            );
        }

        return $utf8Char;
    }

    /**
     * Load binary unicode sequence
     */
    protected function loadBinUnicode()
    {
        // read byte length
        $n = $this->read(4);
        if (static::$isLittleEndian === false) {
            $n = strrev($n);
        }
        list(, $n) = unpack('l', $n);
        $data      = $this->read($n);

        $this->stack[] = $data;
    }

    /**
     * Load a marker sequence
     */
    protected function loadMark()
    {
        $this->stack[] = $this->marker;
    }

    /**
     * Load an array (list)
     */
    protected function loadList()
    {
        $k = $this->lastMarker();
        $this->stack[$k] = array();

        // remove all elements after marker
        for ($i = $k + 1, $max = count($this->stack); $i < $max; $i++) {
            unset($this->stack[$i]);
        }
    }

    /**
     * Load an append (to list) sequence
     */
    protected function loadAppend()
    {
        $value  =  array_pop($this->stack);
        $list   =& $this->stack[count($this->stack) - 1];
        $list[] =  $value;
    }

    /**
     * Load an empty list sequence
     */
    protected function loadEmptyList()
    {
        $this->stack[] = array();
    }

    /**
     * Load multiple append (to list) sequences at once
     */
    protected function loadAppends()
    {
        $k    =  $this->lastMarker();
        $list =& $this->stack[$k - 1];
        $max  =  count($this->stack);
        for ($i = $k + 1; $i < $max; $i++) {
            $list[] = $this->stack[$i];
            unset($this->stack[$i]);
        }
        unset($this->stack[$k]);
    }

    /**
     * Load an associative array (Python dictionary)
     */
    protected function loadDict()
    {
        $k = $this->lastMarker();
        $this->stack[$k] = array();

        // remove all elements after marker
        $max = count($this->stack);
        for ($i = $k + 1; $i < $max; $i++) {
            unset($this->stack[$i]);
        }
    }

    /**
     * Load an item from a set
     */
    protected function loadSetItem()
    {
        $value =  array_pop($this->stack);
        $key   =  array_pop($this->stack);
        $dict  =& $this->stack[count($this->stack) - 1];
        $dict[$key] = $value;
    }

    /**
     * Load an empty dictionary
     */
    protected function _loadEmptyDict()
    {
        $this->stack[] = array();
    }

    /**
     * Load set items
     */
    protected function loadSetItems()
    {
        $k    =  $this->lastMarker();
        $dict =& $this->stack[$k - 1];
        $max  =  count($this->stack);
        for ($i = $k + 1; $i < $max; $i += 2) {
            $key        = $this->stack[$i];
            $value      = $this->stack[$i + 1];
            $dict[$key] = $value;
            unset($this->stack[$i], $this->stack[$i+1]);
        }
        unset($this->stack[$k]);
    }

    /**
     * Load a tuple
     */
    protected function loadTuple()
    {
        $k                =  $this->lastMarker();
        $this->stack[$k]  =  array();
        $tuple            =& $this->stack[$k];
        $max              =  count($this->stack);
        for ($i = $k + 1; $i < $max; $i++) {
            $tuple[] = $this->stack[$i];
            unset($this->stack[$i]);
        }
    }

    /**
     * Load single item tuple
     */
    protected function loadTuple1()
    {
        $value1        = array_pop($this->stack);
        $this->stack[] = array($value1);
    }

    /**
     * Load two item tuple
     *
     */
    protected function loadTuple2()
    {
        $value2 = array_pop($this->stack);
        $value1 = array_pop($this->stack);
        $this->stack[] = array($value1, $value2);
    }

    /**
     * Load three item tuple
     *
     */
    protected function loadTuple3()
    {
        $value3 = array_pop($this->stack);
        $value2 = array_pop($this->stack);
        $value1 = array_pop($this->stack);
        $this->stack[] = array($value1, $value2, $value3);
    }

    /**
     * Load a proto value
     *
     * @throws Exception\RuntimeException if Pickle version does not support this feature
     */
    protected function loadProto()
    {
        $proto = ord($this->read(1));
        if ($proto < 2 || $proto > 3) {
            throw new Exception\RuntimeException(
                "Invalid or unknown protocol version '{$proto}' detected"
            );
        }
        $this->protocol = $proto;
    }

    /* unserialize helper */

    /**
     * Read a segment of the pickle
     *
     * @param  mixed $len
     * @return string
     * @throws Exception\RuntimeException if position matches end of data
     */
    protected function read($len)
    {
        if (($this->pos + $len) > $this->pickleLen) {
            throw new Exception\RuntimeException('End of data');
        }

        $this->pos += $len;
        return substr($this->pickle, ($this->pos - $len), $len);
    }

    /**
     * Read a line of the pickle at once
     *
     * @return string
     * @throws Exception\RuntimeException if no EOL character found
     */
    protected function readline()
    {
        $eolLen = 2;
        $eolPos = strpos($this->pickle, "\r\n", $this->pos);
        if ($eolPos === false) {
            $eolPos = strpos($this->pickle, "\n", $this->pos);
            $eolLen = 1;
        }

        if ($eolPos === false) {
            throw new Exception\RuntimeException('No new line found');
        }
        $ret       = substr($this->pickle, $this->pos, $eolPos-$this->pos);
        $this->pos = $eolPos + $eolLen;

        return $ret;
    }

    /**
     * Unquote/Unescape a quoted string
     *
     * @param  string $str quoted string
     * @return string unquoted string
     */
    protected function unquoteString($str)
    {
        $quoteArr = array_flip(static::$quoteString);

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
    protected function lastMarker()
    {
        for ($k = count($this->stack)-1; $k >= 0; $k -= 1) {
            if ($this->stack[$k] === $this->marker) {
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
    protected function decodeBinLong($data)
    {
        $nbytes = strlen($data);

        if ($nbytes == 0) {
            return 0;
        }

        $long = 0;
        if ($nbytes > 7) {
            if ($this->bigIntegerAdapter === null) {
                $this->bigIntegerAdapter = BigInteger\BigInteger::getDefaultAdapter();
            }
            if (static::$isLittleEndian === true) {
                $data = strrev($data);
            }
            $long = $this->bigIntegerAdapter->binToInt($data, true);
        } else {
            for ($i = 0; $i < $nbytes; $i++) {
                $long += ord($data[$i]) * pow(256, $i);
            }
            if (0x80 <= ord($data[$nbytes - 1])) {
                $long -= pow(2, $nbytes * 8);
                // $long-= 1 << ($nbytes * 8);
            }
        }

        return $long;
    }
}
