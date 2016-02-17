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
 * @package    Zend_Reflection
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Reflection_Class
 */
#require_once 'Zend/Reflection/Class.php';

/**
 * @see Zend_Reflection_Function
 */
#require_once 'Zend/Reflection/Function.php';

/**
 * @category   Zend
 * @package    Zend_Reflection
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Reflection_File implements Reflector
{
    /**
     * @var string
     */
    protected $_filepath        = null;

    /**
     * @var string
     */
    protected $_docComment      = null;

    /**
     * @var int
     */
    protected $_startLine       = 1;

    /**
     * @var int
     */
    protected $_endLine         = null;

    /**
     * @var string[]
     */
    protected $_requiredFiles   = array();

    /**
     * @var Zend_Reflection_Class[]
     */
    protected $_classes         = array();

    /**
     * @var Zend_Reflection_Function[]
     */
    protected $_functions       = array();

    /**
     * @var string
     */
    protected $_contents        = null;

    /**
     * Constructor
     *
     * @param  string $file
     * @return void
     */
    public function __construct($file)
    {
        $fileName = $file;

        $fileRealpath = realpath($fileName);
        if ($fileRealpath) {
            // realpath() doesn't return false if Suhosin is included
            // see http://uk3.php.net/manual/en/function.realpath.php#82770
            if (!file_exists($fileRealpath)) {
                $fileRealpath = false;
            }
        }

        if ($fileRealpath === false) {
            $fileRealpath = self::findRealpathInIncludePath($file);
        }

        if (!$fileRealpath || !in_array($fileRealpath, get_included_files())) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('File ' . $file . ' must be required before it can be reflected');
        }

        $this->_fileName = $fileRealpath;
        $this->_contents = file_get_contents($this->_fileName);
        $this->_reflect();
    }

    /**
     * Find realpath of file based on include_path
     *
     * @param  string $fileName
     * @return string
     */
    public static function findRealpathInIncludePath($fileName)
    {
        #require_once 'Zend/Loader.php';
        $includePaths = Zend_Loader::explodeIncludePath();
        while (count($includePaths) > 0) {
            $filePath = array_shift($includePaths) . DIRECTORY_SEPARATOR . $fileName;

            if ( ($foundRealpath = realpath($filePath)) !== false) {
                break;
            }
        }

        return $foundRealpath;
    }

    /**
     * Export
     *
     * Required by the Reflector interface.
     *
     * @todo   What should this do?
     * @return null
     */
    public static function export()
    {
        return null;
    }

    /**
     * Return the file name of the reflected file
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * Get the start line - Always 1, staying consistent with the Reflection API
     *
     * @return int
     */
    public function getStartLine()
    {
        return $this->_startLine;
    }

    /**
     * Get the end line / number of lines
     *
     * @return int
     */
    public function getEndLine()
    {
        return $this->_endLine;
    }

    /**
     * Return the doc comment
     *
     * @return string
     */
    public function getDocComment()
    {
        return $this->_docComment;
    }

    /**
     * Return the docblock
     *
     * @param  string $reflectionClass Reflection class to use
     * @return Zend_Reflection_Docblock
     */
    public function getDocblock($reflectionClass = 'Zend_Reflection_Docblock')
    {
        $instance = new $reflectionClass($this);
        if (!$instance instanceof Zend_Reflection_Docblock) {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Invalid reflection class specified; must extend Zend_Reflection_Docblock');
        }
        return $instance;
    }

    /**
     * Return the reflection classes of the classes found inside this file
     *
     * @param  string $reflectionClass Name of reflection class to use for instances
     * @return array Array of Zend_Reflection_Class instances
     */
    public function getClasses($reflectionClass = 'Zend_Reflection_Class')
    {
        $classes = array();
        foreach ($this->_classes as $class) {
            $instance = new $reflectionClass($class);
            if (!$instance instanceof Zend_Reflection_Class) {
                #require_once 'Zend/Reflection/Exception.php';
                throw new Zend_Reflection_Exception('Invalid reflection class provided; must extend Zend_Reflection_Class');
            }
            $classes[] = $instance;
        }
        return $classes;
    }

    /**
     * Return the reflection functions of the functions found inside this file
     *
     * @param  string $reflectionClass Name of reflection class to use for instances
     * @return array Array of Zend_Reflection_Functions
     */
    public function getFunctions($reflectionClass = 'Zend_Reflection_Function')
    {
        $functions = array();
        foreach ($this->_functions as $function) {
            $instance = new $reflectionClass($function);
            if (!$instance instanceof Zend_Reflection_Function) {
                #require_once 'Zend/Reflection/Exception.php';
                throw new Zend_Reflection_Exception('Invalid reflection class provided; must extend Zend_Reflection_Function');
            }
            $functions[] = $instance;
        }
        return $functions;
    }

    /**
     * Retrieve the reflection class of a given class found in this file
     *
     * @param  null|string $name
     * @param  string $reflectionClass Reflection class to use when creating reflection instance
     * @return Zend_Reflection_Class
     * @throws Zend_Reflection_Exception for invalid class name or invalid reflection class
     */
    public function getClass($name = null, $reflectionClass = 'Zend_Reflection_Class')
    {
        if ($name === null) {
            reset($this->_classes);
            $selected = current($this->_classes);
            $instance = new $reflectionClass($selected);
            if (!$instance instanceof Zend_Reflection_Class) {
                #require_once 'Zend/Reflection/Exception.php';
                throw new Zend_Reflection_Exception('Invalid reflection class given; must extend Zend_Reflection_Class');
            }
            return $instance;
        }

        if (in_array($name, $this->_classes)) {
            $instance = new $reflectionClass($name);
            if (!$instance instanceof Zend_Reflection_Class) {
                #require_once 'Zend/Reflection/Exception.php';
                throw new Zend_Reflection_Exception('Invalid reflection class given; must extend Zend_Reflection_Class');
            }
            return $instance;
        }

        #require_once 'Zend/Reflection/Exception.php';
        throw new Zend_Reflection_Exception('Class by name ' . $name . ' not found.');
    }

    /**
     * Return the full contents of file
     *
     * @return string
     */
    public function getContents()
    {
        return $this->_contents;
    }

    /**
     * Serialize to string
     *
     * Required by the Reflector interface
     *
     * @todo   What should this serialization look like?
     * @return string
     */
    public function __toString()
    {
        return '';
    }

    /**
     * This method does the work of "reflecting" the file
     *
     * Uses PHP's tokenizer to perform file reflection.
     *
     * @return void
     */
    protected function _reflect()
    {
        $contents = $this->_contents;
        $tokens   = token_get_all($contents);

        $functionTrapped           = false;
        $classTrapped              = false;
        $requireTrapped            = false;
        $embeddedVariableTrapped   = false;
        $openBraces                = 0;

        $this->_checkFileDocBlock($tokens);

        foreach ($tokens as $token) {
            /*
             * Tokens are characters representing symbols or arrays
             * representing strings. The keys/values in the arrays are
             *
             * - 0 => token id,
             * - 1 => string,
             * - 2 => line number
             *
             * Token ID's are explained here:
             * http://www.php.net/manual/en/tokens.php.
             */

            if (is_array($token)) {
                $type    = $token[0];
                $value   = $token[1];
                $lineNum = $token[2];
            } else {
                // It's a symbol
                // Maintain the count of open braces
                if ($token == '{') {
                    $openBraces++;
                } else if ($token == '}') {
                    if ( $embeddedVariableTrapped ) {
                        $embeddedVariableTrapped = false;
                    } else {
                        $openBraces--;
                    }
                }

                continue;
            }

            switch ($type) {
                case T_STRING_VARNAME:
                case T_DOLLAR_OPEN_CURLY_BRACES:
                case T_CURLY_OPEN:
                    $embeddedVariableTrapped = true;
                    continue;

                // Name of something
                case T_STRING:
                    if ($functionTrapped) {
                        $this->_functions[] = $value;
                        $functionTrapped = false;
                    } elseif ($classTrapped) {
                        $this->_classes[] = $value;
                        $classTrapped = false;
                    }
                    continue;

                // Required file names are T_CONSTANT_ENCAPSED_STRING
                case T_CONSTANT_ENCAPSED_STRING:
                    if ($requireTrapped) {
                        $this->_requiredFiles[] = $value ."\n";
                        $requireTrapped = false;
                    }
                    continue;

                // Functions
                case T_FUNCTION:
                    if ($openBraces == 0) {
                        $functionTrapped = true;
                    }
                    break;

                // Classes
                case T_CLASS:
                case T_INTERFACE:
                    $classTrapped = true;
                    break;

                // All types of requires
                case T_REQUIRE:
                case T_REQUIRE_ONCE:
                case T_INCLUDE:
                case T_INCLUDE_ONCE:
                    $requireTrapped = true;
                    break;

                // Default case: do nothing
                default:
                    break;
            }
        }

        $this->_endLine = count(explode("\n", $this->_contents));
    }

    /**
     * Validate / check a file level docblock
     *
     * @param  array $tokens Array of tokenizer tokens
     * @return void
     */
    protected function _checkFileDocBlock($tokens) {
        foreach ($tokens as $token) {
            $type    = $token[0];
            $value   = $token[1];
            $lineNum = $token[2];
            if(($type == T_OPEN_TAG) || ($type == T_WHITESPACE)) {
                continue;
            } elseif ($type == T_DOC_COMMENT) {
                $this->_docComment = $value;
                $this->_startLine  = $lineNum + substr_count($value, "\n") + 1;
                return;
            } else {
                // Only whitespace is allowed before file docblocks
                return;
            }
        }
    }
}
