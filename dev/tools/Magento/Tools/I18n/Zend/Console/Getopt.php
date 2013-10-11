<?php
/**
 * Zend_Console_Getopt is a class to parse options for command-line
 * applications.
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
 * @package    Zend_Console_Getopt
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Getopt.php 22191 2010-05-17 21:50:14Z jan $
 */

/**
 * Zend_Console_Getopt is a class to parse options for command-line
 * applications.
 *
 * Terminology:
 * Argument: an element of the argv array.  This may be part of an option,
 *   or it may be a non-option command-line argument.
 * Flag: the letter or word set off by a '-' or '--'.  Example: in '--output filename',
 *   '--output' is the flag.
 * Parameter: the additional argument that is associated with the option.
 *   Example: in '--output filename', the 'filename' is the parameter.
 * Option: the combination of a flag and its parameter, if any.
 *   Example: in '--output filename', the whole thing is the option.
 *
 * The following features are supported:
 *
 * - Short flags like '-a'.  Short flags are preceded by a single
 *   dash.  Short flags may be clustered e.g. '-abc', which is the
 *   same as '-a' '-b' '-c'.
 * - Long flags like '--verbose'.  Long flags are preceded by a
 *   double dash.  Long flags may not be clustered.
 * - Options may have a parameter, e.g. '--output filename'.
 * - Parameters for long flags may also be set off with an equals sign,
 *   e.g. '--output=filename'.
 * - Parameters for long flags may be checked as string, word, or integer.
 * - Automatic generation of a helpful usage message.
 * - Signal end of options with '--'; subsequent arguments are treated
 *   as non-option arguments, even if they begin with '-'.
 * - Raise exception Zend_Console_Getopt_Exception in several cases
 *   when invalid flags or parameters are given.  Usage message is
 *   returned in the exception object.
 *
 * The format for specifying options uses a PHP associative array.
 * The key is has the format of a list of pipe-separated flag names,
 * followed by an optional '=' to indicate a required parameter or
 * '-' to indicate an optional parameter.  Following that, the type
 * of parameter may be specified as 's' for string, 'w' for word,
 * or 'i' for integer.
 *
 * Examples:
 * - 'user|username|u=s'  this means '--user' or '--username' or '-u'
 *   are synonyms, and the option requires a string parameter.
 * - 'p=i'  this means '-p' requires an integer parameter.  No synonyms.
 * - 'verbose|v-i'  this means '--verbose' or '-v' are synonyms, and
 *   they take an optional integer parameter.
 * - 'help|h'  this means '--help' or '-h' are synonyms, and
 *   they take no parameter.
 *
 * The values in the associative array are strings that are used as
 * brief descriptions of the options when printing a usage message.
 *
 * The simpler format for specifying options used by PHP's getopt()
 * function is also supported.  This is similar to GNU getopt and shell
 * getopt format.
 *
 * Example:  'abc:' means options '-a', '-b', and '-c'
 * are legal, and the latter requires a string parameter.
 *
 * @category   Zend
 * @package    Zend_Console_Getopt
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.6.0
 *
 * @todo  Handle params with multiple values, e.g. --colors=red,green,blue
 *        Set value of parameter to the array of values.  Allow user to specify
 *        the separator with Zend_Console_Getopt::CONFIG_PARAMETER_SEPARATOR.
 *        If this config value is null or empty string, do not split values
 *        into arrays.  Default separator is comma (',').
 *
 * @todo  Handle params with multiple values specified with separate options
 *        e.g. --colors red --colors green --colors blue should give one
 *        option with an array(red, green, blue).
 *        Enable with Zend_Console_Getopt::CONFIG_CUMULATIVE_PARAMETERS.
 *        Default is that subsequent options overwrite the parameter value.
 *
 * @todo  Handle flags occurring multiple times, e.g. -v -v -v
 *        Set value of the option's parameter to the integer count of instances
 *        instead of a boolean.
 *        Enable with Zend_Console_Getopt::CONFIG_CUMULATIVE_FLAGS.
 *        Default is that the value is simply boolean true regardless of
 *        how many instances of the flag appear.
 *
 * @todo  Handle flags that implicitly print usage message, e.g. --help
 *
 * @todo  Handle freeform options, e.g. --set-variable
 *        Enable with Zend_Console_Getopt::CONFIG_FREEFORM_FLAGS
 *        All flag-like syntax is recognized, no flag generates an exception.
 *
 * @todo  Handle numeric options, e.g. -1, -2, -3, -1000
 *        Enable with Zend_Console_Getopt::CONFIG_NUMERIC_FLAGS
 *        The rule must specify a named flag and the '#' symbol as the
 *        parameter type. e.g.,  'lines=#'
 *
 * @todo  Enable user to specify header and footer content in the help message.
 *
 * @todo  Feature request to handle option interdependencies.
 *        e.g. if -b is specified, -a must be specified or else the
 *        usage is invalid.
 *
 * @todo  Feature request to implement callbacks.
 *        e.g. if -a is specified, run function 'handleOptionA'().
 */
class Zend_Console_Getopt
{

    /**
     * The options for a given application can be in multiple formats.
     * modeGnu is for traditional 'ab:c:' style getopt format.
     * modeZend is for a more structured format.
     */
    const MODE_ZEND                         = 'zend';
    const MODE_GNU                          = 'gnu';

    /**
     * Constant tokens for various symbols used in the mode_zend
     * rule format.
     */
    const PARAM_REQUIRED                    = '=';
    const PARAM_OPTIONAL                    = '-';
    const TYPE_STRING                       = 's';
    const TYPE_WORD                         = 'w';
    const TYPE_INTEGER                      = 'i';

    /**
     * These are constants for optional behavior of this class.
     * ruleMode is either 'zend' or 'gnu' or a user-defined mode.
     * dashDash is true if '--' signifies the end of command-line options.
     * ignoreCase is true if '--opt' and '--OPT' are implicitly synonyms.
     * parseAll is true if all options on the command line should be parsed, regardless of
     * whether an argument appears before them.
     */
    const CONFIG_RULEMODE                   = 'ruleMode';
    const CONFIG_DASHDASH                   = 'dashDash';
    const CONFIG_IGNORECASE                 = 'ignoreCase';
    const CONFIG_PARSEALL                   = 'parseAll';

    /**
     * Defaults for getopt configuration are:
     * ruleMode is 'zend' format,
     * dashDash (--) token is enabled,
     * ignoreCase is not enabled,
     * parseAll is enabled.
     */
    protected $_getoptConfig = array(
        self::CONFIG_RULEMODE   => self::MODE_ZEND,
        self::CONFIG_DASHDASH   => true,
        self::CONFIG_IGNORECASE => false,
        self::CONFIG_PARSEALL   => true,
    );

    /**
     * Stores the command-line arguments for the calling applicaion.
     *
     * @var array
     */
    protected $_argv = array();

    /**
     * Stores the name of the calling applicaion.
     *
     * @var string
     */
    protected $_progname = '';

    /**
     * Stores the list of legal options for this application.
     *
     * @var array
     */
    protected $_rules = array();

    /**
     * Stores alternate spellings of legal options.
     *
     * @var array
     */
    protected $_ruleMap = array();

    /**
     * Stores options given by the user in the current invocation
     * of the application, as well as parameters given in options.
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Stores the command-line arguments other than options.
     *
     * @var array
     */
    protected $_remainingArgs = array();

    /**
     * State of the options: parsed or not yet parsed?
     *
     * @var boolean
     */
    protected $_parsed = false;

    /**
     * The constructor takes one to three parameters.
     *
     * The first parameter is $rules, which may be a string for
     * gnu-style format, or a structured array for Zend-style format.
     *
     * The second parameter is $argv, and it is optional.  If not
     * specified, $argv is inferred from the global argv.
     *
     * The third parameter is an array of configuration parameters
     * to control the behavior of this instance of Getopt; it is optional.
     *
     * @param  array $rules
     * @param  array $argv
     * @param  array $getoptConfig
     * @return void
     */
    public function __construct($rules, $argv = null, $getoptConfig = array())
    {
        if (!isset($_SERVER['argv'])) {
            #require_once 'Zend/Console/Getopt/Exception.php';
            if (ini_get('register_argc_argv') == false) {
                throw new Zend_Console_Getopt_Exception(
                    "argv is not available, because ini option 'register_argc_argv' is set Off"
                );
            } else {
                throw new Zend_Console_Getopt_Exception(
                    '$_SERVER["argv"] is not set, but Zend_Console_Getopt cannot work without this information.'
                );
            }
        }

        $this->_progname = $_SERVER['argv'][0];
        $this->setOptions($getoptConfig);
        $this->addRules($rules);
        if (!is_array($argv)) {
            $argv = array_slice($_SERVER['argv'], 1);
        }
        if (isset($argv)) {
            $this->addArguments((array)$argv);
        }
    }

    /**
     * Return the state of the option seen on the command line of the
     * current application invocation.  This function returns true, or the
     * parameter to the option, if any.  If the option was not given,
     * this function returns null.
     *
     * The magic __get method works in the context of naming the option
     * as a virtual member of this class.
     *
     * @param  string $key
     * @return string
     */
    public function __get($key)
    {
        return $this->getOption($key);
    }

    /**
     * Test whether a given option has been seen.
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        $this->parse();
        if (isset($this->_ruleMap[$key])) {
            $key = $this->_ruleMap[$key];
            return isset($this->_options[$key]);
        }
        return false;
    }

    /**
     * Set the value for a given option.
     *
     * @param  string $key
     * @param  string $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->parse();
        if (isset($this->_ruleMap[$key])) {
            $key = $this->_ruleMap[$key];
            $this->_options[$key] = $value;
        }
    }

    /**
     * Return the current set of options and parameters seen as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Unset an option.
     *
     * @param  string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->parse();
        if (isset($this->_ruleMap[$key])) {
            $key = $this->_ruleMap[$key];
            unset($this->_options[$key]);
        }
    }

    /**
     * Define additional command-line arguments.
     * These are appended to those defined when the constructor was called.
     *
     * @param  array $argv
     * @throws Zend_Console_Getopt_Exception When not given an array as parameter
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function addArguments($argv)
    {
        if(!is_array($argv)) {
            #require_once 'Zend/Console/Getopt/Exception.php';
            throw new Zend_Console_Getopt_Exception(
                "Parameter #1 to addArguments should be an array");
        }
        $this->_argv = array_merge($this->_argv, $argv);
        $this->_parsed = false;
        return $this;
    }

    /**
     * Define full set of command-line arguments.
     * These replace any currently defined.
     *
     * @param  array $argv
     * @throws Zend_Console_Getopt_Exception When not given an array as parameter
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function setArguments($argv)
    {
        if(!is_array($argv)) {
            #require_once 'Zend/Console/Getopt/Exception.php';
            throw new Zend_Console_Getopt_Exception(
                "Parameter #1 to setArguments should be an array");
        }
        $this->_argv = $argv;
        $this->_parsed = false;
        return $this;
    }

    /**
     * Define multiple configuration options from an associative array.
     * These are not program options, but properties to configure
     * the behavior of Zend_Console_Getopt.
     *
     * @param  array $getoptConfig
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function setOptions($getoptConfig)
    {
        if (isset($getoptConfig)) {
            foreach ($getoptConfig as $key => $value) {
                $this->setOption($key, $value);
            }
        }
        return $this;
    }

    /**
     * Define one configuration option as a key/value pair.
     * These are not program options, but properties to configure
     * the behavior of Zend_Console_Getopt.
     *
     * @param  string $configKey
     * @param  string $configValue
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function setOption($configKey, $configValue)
    {
        if ($configKey !== null) {
            $this->_getoptConfig[$configKey] = $configValue;
        }
        return $this;
    }

    /**
     * Define additional option rules.
     * These are appended to the rules defined when the constructor was called.
     *
     * @param  array $rules
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function addRules($rules)
    {
        $ruleMode = $this->_getoptConfig['ruleMode'];
        switch ($this->_getoptConfig['ruleMode']) {
            case self::MODE_ZEND:
                if (is_array($rules)) {
                    $this->_addRulesModeZend($rules);
                    break;
                }
                // intentional fallthrough
            case self::MODE_GNU:
                $this->_addRulesModeGnu($rules);
                break;
            default:
                /**
                 * Call addRulesModeFoo() for ruleMode 'foo'.
                 * The developer should subclass Getopt and
                 * provide this method.
                 */
                $method = '_addRulesMode' . ucfirst($ruleMode);
                $this->$method($rules);
        }
        $this->_parsed = false;
        return $this;
    }

    /**
     * Return the current set of options and parameters seen as a string.
     *
     * @return string
     */
    public function toString()
    {
        $this->parse();
        $s = array();
        foreach ($this->_options as $flag => $value) {
            $s[] = $flag . '=' . ($value === true ? 'true' : $value);
        }
        return implode(' ', $s);
    }

    /**
     * Return the current set of options and parameters seen
     * as an array of canonical options and parameters.
     *
     * Clusters have been expanded, and option aliases
     * have been mapped to their primary option names.
     *
     * @return array
     */
    public function toArray()
    {
        $this->parse();
        $s = array();
        foreach ($this->_options as $flag => $value) {
            $s[] = $flag;
            if ($value !== true) {
                $s[] = $value;
            }
        }
        return $s;
    }

    /**
     * Return the current set of options and parameters seen in Json format.
     *
     * @return string
     */
    public function toJson()
    {
        $this->parse();
        $j = array();
        foreach ($this->_options as $flag => $value) {
            $j['options'][] = array(
                'option' => array(
                    'flag' => $flag,
                    'parameter' => $value
                )
            );
        }

        /**
         * @see Zend_Json
         */
        #require_once 'Zend/Json.php';
        $json = Zend_Json::encode($j);

        return $json;
    }

    /**
     * Return the current set of options and parameters seen in XML format.
     *
     * @return string
     */
    public function toXml()
    {
        $this->parse();
        $doc = new DomDocument('1.0', 'utf-8');
        $optionsNode = $doc->createElement('options');
        $doc->appendChild($optionsNode);
        foreach ($this->_options as $flag => $value) {
            $optionNode = $doc->createElement('option');
            $optionNode->setAttribute('flag', utf8_encode($flag));
            if ($value !== true) {
                $optionNode->setAttribute('parameter', utf8_encode($value));
            }
            $optionsNode->appendChild($optionNode);
        }
        $xml = $doc->saveXML();
        return $xml;
    }

    /**
     * Return a list of options that have been seen in the current argv.
     *
     * @return array
     */
    public function getOptions()
    {
        $this->parse();
        return array_keys($this->_options);
    }

    /**
     * Return the state of the option seen on the command line of the
     * current application invocation.
     *
     * This function returns true, or the parameter value to the option, if any.
     * If the option was not given, this function returns false.
     *
     * @param  string $flag
     * @return mixed
     */
    public function getOption($flag)
    {
        $this->parse();
        if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
            $flag = strtolower($flag);
        }
        if (isset($this->_ruleMap[$flag])) {
            $flag = $this->_ruleMap[$flag];
            if (isset($this->_options[$flag])) {
                return $this->_options[$flag];
            }
        }
        return null;
    }

    /**
     * Return the arguments from the command-line following all options found.
     *
     * @return array
     */
    public function getRemainingArgs()
    {
        $this->parse();
        return $this->_remainingArgs;
    }

    /**
     * Return a useful option reference, formatted for display in an
     * error message.
     *
     * Note that this usage information is provided in most Exceptions
     * generated by this class.
     *
     * @return string
     */
    public function getUsageMessage()
    {
        $usage = "Usage: {$this->_progname} [ options ]\n";
        $maxLen = 20;
        $lines = array();
        foreach ($this->_rules as $rule) {
            $flags = array();
            if (is_array($rule['alias'])) {
                foreach ($rule['alias'] as $flag) {
                    $flags[] = (strlen($flag) == 1 ? '-' : '--') . $flag;
                }
            }
            $linepart['name'] = implode('|', $flags);
            if (isset($rule['param']) && $rule['param'] != 'none') {
                $linepart['name'] .= ' ';
                switch ($rule['param']) {
                    case 'optional':
                        $linepart['name'] .= "[ <{$rule['paramType']}> ]";
                        break;
                    case 'required':
                        $linepart['name'] .= "<{$rule['paramType']}>";
                        break;
                }
            }
            if (strlen($linepart['name']) > $maxLen) {
                $maxLen = strlen($linepart['name']);
            }
            $linepart['help'] = '';
            if (isset($rule['help'])) {
                $linepart['help'] .= $rule['help'];
            }
            $lines[] = $linepart;
        }
        foreach ($lines as $linepart) {
            $usage .= sprintf("%s %s\n",
            str_pad($linepart['name'], $maxLen),
            $linepart['help']);
        }
        return $usage;
    }

    /**
     * Define aliases for options.
     *
     * The parameter $aliasMap is an associative array
     * mapping option name (short or long) to an alias.
     *
     * @param  array $aliasMap
     * @throws Zend_Console_Getopt_Exception
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function setAliases($aliasMap)
    {
        foreach ($aliasMap as $flag => $alias)
        {
            if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
                $flag = strtolower($flag);
                $alias = strtolower($alias);
            }
            if (!isset($this->_ruleMap[$flag])) {
                continue;
            }
            $flag = $this->_ruleMap[$flag];
            if (isset($this->_rules[$alias]) || isset($this->_ruleMap[$alias])) {
                $o = (strlen($alias) == 1 ? '-' : '--') . $alias;
                #require_once 'Zend/Console/Getopt/Exception.php';
                throw new Zend_Console_Getopt_Exception(
                    "Option \"$o\" is being defined more than once.");
            }
            $this->_rules[$flag]['alias'][] = $alias;
            $this->_ruleMap[$alias] = $flag;
        }
        return $this;
    }

    /**
     * Define help messages for options.
     *
     * The parameter $help_map is an associative array
     * mapping option name (short or long) to the help string.
     *
     * @param  array $helpMap
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function setHelp($helpMap)
    {
        foreach ($helpMap as $flag => $help)
        {
            if (!isset($this->_ruleMap[$flag])) {
                continue;
            }
            $flag = $this->_ruleMap[$flag];
            $this->_rules[$flag]['help'] = $help;
        }
        return $this;
    }

    /**
     * Parse command-line arguments and find both long and short
     * options.
     *
     * Also find option parameters, and remaining arguments after
     * all options have been parsed.
     *
     * @return Zend_Console_Getopt|null Provides a fluent interface
     */
    public function parse()
    {
        if ($this->_parsed === true) {
            return;
        }
        $argv = $this->_argv;
        $this->_options = array();
        $this->_remainingArgs = array();
        while (count($argv) > 0) {
            if ($argv[0] == '--') {
                array_shift($argv);
                if ($this->_getoptConfig[self::CONFIG_DASHDASH]) {
                    $this->_remainingArgs = array_merge($this->_remainingArgs, $argv);
                    break;
                }
            }
            if (substr($argv[0], 0, 2) == '--') {
                $this->_parseLongOption($argv);
            } else if (substr($argv[0], 0, 1) == '-' && ('-' != $argv[0] || count($argv) >1))  {
                $this->_parseShortOptionCluster($argv);
            } else if($this->_getoptConfig[self::CONFIG_PARSEALL]) {
                $this->_remainingArgs[] = array_shift($argv);
            } else {
                /*
                 * We should put all other arguments in _remainingArgs and stop parsing
                 * since CONFIG_PARSEALL is false.
                 */
                $this->_remainingArgs = array_merge($this->_remainingArgs, $argv);
                break;
            }
        }
        $this->_parsed = true;
        return $this;
    }

    /**
     * Parse command-line arguments for a single long option.
     * A long option is preceded by a double '--' character.
     * Long options may not be clustered.
     *
     * @param  mixed &$argv
     * @return void
     */
    protected function _parseLongOption(&$argv)
    {
        $optionWithParam = ltrim(array_shift($argv), '-');
        $l = explode('=', $optionWithParam, 2);
        $flag = array_shift($l);
        $param = array_shift($l);
        if (isset($param)) {
            array_unshift($argv, $param);
        }
        $this->_parseSingleOption($flag, $argv);
    }

    /**
     * Parse command-line arguments for short options.
     * Short options are those preceded by a single '-' character.
     * Short options may be clustered.
     *
     * @param  mixed &$argv
     * @return void
     */
    protected function _parseShortOptionCluster(&$argv)
    {
        $flagCluster = ltrim(array_shift($argv), '-');
        foreach (str_split($flagCluster) as $flag) {
            $this->_parseSingleOption($flag, $argv);
        }
    }

    /**
     * Parse command-line arguments for a single option.
     *
     * @param  string $flag
     * @param  mixed  $argv
     * @throws Zend_Console_Getopt_Exception
     * @return void
     */
    protected function _parseSingleOption($flag, &$argv)
    {
        if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
            $flag = strtolower($flag);
        }
        if (!isset($this->_ruleMap[$flag])) {
            #require_once 'Zend/Console/Getopt/Exception.php';
            throw new Zend_Console_Getopt_Exception(
                "Option \"$flag\" is not recognized.",
                $this->getUsageMessage());
        }
        $realFlag = $this->_ruleMap[$flag];
        switch ($this->_rules[$realFlag]['param']) {
            case 'required':
                if (count($argv) > 0) {
                    $param = array_shift($argv);
                    $this->_checkParameterType($realFlag, $param);
                } else {
                    #require_once 'Zend/Console/Getopt/Exception.php';
                    throw new Zend_Console_Getopt_Exception(
                        "Option \"$flag\" requires a parameter.",
                        $this->getUsageMessage());
                }
                break;
            case 'optional':
                if (count($argv) > 0 && substr($argv[0], 0, 1) != '-') {
                    $param = array_shift($argv);
                    $this->_checkParameterType($realFlag, $param);
                } else {
                    $param = true;
                }
                break;
            default:
                $param = true;
        }
        $this->_options[$realFlag] = $param;
    }

    /**
     * Return true if the parameter is in a valid format for
     * the option $flag.
     * Throw an exception in most other cases.
     *
     * @param  string $flag
     * @param  string $param
     * @throws Zend_Console_Getopt_Exception
     * @return bool
     */
    protected function _checkParameterType($flag, $param)
    {
        $type = 'string';
        if (isset($this->_rules[$flag]['paramType'])) {
            $type = $this->_rules[$flag]['paramType'];
        }
        switch ($type) {
            case 'word':
                if (preg_match('/\W/', $param)) {
                    #require_once 'Zend/Console/Getopt/Exception.php';
                    throw new Zend_Console_Getopt_Exception(
                        "Option \"$flag\" requires a single-word parameter, but was given \"$param\".",
                        $this->getUsageMessage());
                }
                break;
            case 'integer':
                if (preg_match('/\D/', $param)) {
                    #require_once 'Zend/Console/Getopt/Exception.php';
                    throw new Zend_Console_Getopt_Exception(
                        "Option \"$flag\" requires an integer parameter, but was given \"$param\".",
                        $this->getUsageMessage());
                }
                break;
            case 'string':
            default:
                break;
        }
        return true;
    }

    /**
     * Define legal options using the gnu-style format.
     *
     * @param  string $rules
     * @return void
     */
    protected function _addRulesModeGnu($rules)
    {
        $ruleArray = array();

        /**
         * Options may be single alphanumeric characters.
         * Options may have a ':' which indicates a required string parameter.
         * No long options or option aliases are supported in GNU style.
         */
        preg_match_all('/([a-zA-Z0-9]:?)/', $rules, $ruleArray);
        foreach ($ruleArray[1] as $rule) {
            $r = array();
            $flag = substr($rule, 0, 1);
            if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
                $flag = strtolower($flag);
            }
            $r['alias'][] = $flag;
            if (substr($rule, 1, 1) == ':') {
                $r['param'] = 'required';
                $r['paramType'] = 'string';
            } else {
                $r['param'] = 'none';
            }
            $this->_rules[$flag] = $r;
            $this->_ruleMap[$flag] = $flag;
        }
    }

    /**
     * Define legal options using the Zend-style format.
     *
     * @param  array $rules
     * @throws Zend_Console_Getopt_Exception
     * @return void
     */
    protected function _addRulesModeZend($rules)
    {
        foreach ($rules as $ruleCode => $helpMessage)
        {
            // this may have to translate the long parm type if there
            // are any complaints that =string will not work (even though that use
            // case is not documented)
            if (in_array(substr($ruleCode, -2, 1), array('-', '='))) {
                $flagList  = substr($ruleCode, 0, -2);
                $delimiter = substr($ruleCode, -2, 1);
                $paramType = substr($ruleCode, -1);
            } else {
                $flagList = $ruleCode;
                $delimiter = $paramType = null;
            }
            if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
                $flagList = strtolower($flagList);
            }
            $flags = explode('|', $flagList);
            $rule = array();
            $mainFlag = $flags[0];
            foreach ($flags as $flag) {
                if (empty($flag)) {
                    #require_once 'Zend/Console/Getopt/Exception.php';
                    throw new Zend_Console_Getopt_Exception(
                        "Blank flag not allowed in rule \"$ruleCode\".");
                }
                if (strlen($flag) == 1) {
                    if (isset($this->_ruleMap[$flag])) {
                        #require_once 'Zend/Console/Getopt/Exception.php';
                        throw new Zend_Console_Getopt_Exception(
                            "Option \"-$flag\" is being defined more than once.");
                    }
                    $this->_ruleMap[$flag] = $mainFlag;
                    $rule['alias'][] = $flag;
                } else {
                    if (isset($this->_rules[$flag]) || isset($this->_ruleMap[$flag])) {
                        #require_once 'Zend/Console/Getopt/Exception.php';
                        throw new Zend_Console_Getopt_Exception(
                            "Option \"--$flag\" is being defined more than once.");
                    }
                    $this->_ruleMap[$flag] = $mainFlag;
                    $rule['alias'][] = $flag;
                }
            }
            if (isset($delimiter)) {
                switch ($delimiter) {
                    case self::PARAM_REQUIRED:
                        $rule['param'] = 'required';
                        break;
                    case self::PARAM_OPTIONAL:
                    default:
                        $rule['param'] = 'optional';
                }
                switch (substr($paramType, 0, 1)) {
                    case self::TYPE_WORD:
                        $rule['paramType'] = 'word';
                        break;
                    case self::TYPE_INTEGER:
                        $rule['paramType'] = 'integer';
                        break;
                    case self::TYPE_STRING:
                    default:
                        $rule['paramType'] = 'string';
                }
            } else {
                $rule['param'] = 'none';
            }
            $rule['help'] = $helpMessage;
            $this->_rules[$mainFlag] = $rule;
        }
    }

}
