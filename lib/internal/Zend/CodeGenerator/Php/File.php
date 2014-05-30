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
 * @package    Zend_CodeGenerator
 * @subpackage PHP
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: File.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_CodeGenerator_Php_Abstract
 */
#require_once 'Zend/CodeGenerator/Php/Abstract.php';

/**
 * @see Zend_CodeGenerator_Php_Class
 */
#require_once 'Zend/CodeGenerator/Php/Class.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_CodeGenerator_Php_File extends Zend_CodeGenerator_Php_Abstract
{

    /**
     * @var array Array of Zend_CodeGenerator_Php_File
     */
    protected static $_fileCodeGenerators = array();

    /**#@+
     * @var string
     */
    protected static $_markerDocblock = '/* Zend_CodeGenerator_Php_File-DocblockMarker */';
    protected static $_markerRequire = '/* Zend_CodeGenerator_Php_File-RequireMarker: {?} */';
    protected static $_markerClass = '/* Zend_CodeGenerator_Php_File-ClassMarker: {?} */';
    /**#@-*/

    /**
     * @var string
     */
    protected $_filename = null;

    /**
     * @var Zend_CodeGenerator_Php_Docblock
     */
    protected $_docblock = null;

    /**
     * @var array
     */
    protected $_requiredFiles = array();

    /**
     * @var array
     */
    protected $_classes = array();

    /**
     * @var string
     */
    protected $_body = null;

    public static function registerFileCodeGenerator(Zend_CodeGenerator_Php_File $fileCodeGenerator, $fileName = null)
    {
        if ($fileName == null) {
            $fileName = $fileCodeGenerator->getFilename();
        }

        if ($fileName == '') {
            #require_once 'Zend/CodeGenerator/Php/Exception.php';
            throw new Zend_CodeGenerator_Php_Exception('FileName does not exist.');
        }

        // cannot use realpath since the file might not exist, but we do need to have the index
        // in the same DIRECTORY_SEPARATOR that realpath would use:
        $fileName = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $fileName);

        self::$_fileCodeGenerators[$fileName] = $fileCodeGenerator;

    }

    /**
     * fromReflectedFilePath() - use this if you intend on generating code generation objects based on the same file.
     * This will keep previous changes to the file in tact during the same PHP process
     *
     * @param string $filePath
     * @param bool $usePreviousCodeGeneratorIfItExists
     * @param bool $includeIfNotAlreadyIncluded
     * @return Zend_CodeGenerator_Php_File
     */
    public static function fromReflectedFileName($filePath, $usePreviousCodeGeneratorIfItExists = true, $includeIfNotAlreadyIncluded = true)
    {
        $realpath = realpath($filePath);

        if ($realpath === false) {
            if ( ($realpath = Zend_Reflection_file::findRealpathInIncludePath($filePath)) === false) {
                #require_once 'Zend/CodeGenerator/Php/Exception.php';
                throw new Zend_CodeGenerator_Php_Exception('No file for ' . $realpath . ' was found.');
            }
        }

        if ($usePreviousCodeGeneratorIfItExists && isset(self::$_fileCodeGenerators[$realpath])) {
            return self::$_fileCodeGenerators[$realpath];
        }

        if ($includeIfNotAlreadyIncluded && !in_array($realpath, get_included_files())) {
            include $realpath;
        }

        $codeGenerator = self::fromReflection(($fileReflector = new Zend_Reflection_File($realpath)));

        if (!isset(self::$_fileCodeGenerators[$fileReflector->getFileName()])) {
            self::$_fileCodeGenerators[$fileReflector->getFileName()] = $codeGenerator;
        }

        return $codeGenerator;
    }

    /**
     * fromReflection()
     *
     * @param Zend_Reflection_File $reflectionFile
     * @return Zend_CodeGenerator_Php_File
     */
    public static function fromReflection(Zend_Reflection_File $reflectionFile)
    {
        $file = new self();

        $file->setSourceContent($reflectionFile->getContents());
        $file->setSourceDirty(false);

        $body = $reflectionFile->getContents();

        // @todo this whole area needs to be reworked with respect to how body lines are processed
        foreach ($reflectionFile->getClasses() as $class) {
            $file->setClass(Zend_CodeGenerator_Php_Class::fromReflection($class));
            $classStartLine = $class->getStartLine(true);
            $classEndLine = $class->getEndLine();

            $bodyLines = explode("\n", $body);
            $bodyReturn = array();
            for ($lineNum = 1; $lineNum <= count($bodyLines); $lineNum++) {
                if ($lineNum == $classStartLine) {
                    $bodyReturn[] = str_replace('?', $class->getName(), self::$_markerClass);  //'/* Zend_CodeGenerator_Php_File-ClassMarker: {' . $class->getName() . '} */';
                    $lineNum = $classEndLine;
                } else {
                    $bodyReturn[] = $bodyLines[$lineNum - 1]; // adjust for index -> line conversion
                }
            }
            $body = implode("\n", $bodyReturn);
            unset($bodyLines, $bodyReturn, $classStartLine, $classEndLine);
        }

        if (($reflectionFile->getDocComment() != '')) {
            $docblock = $reflectionFile->getDocblock();
            $file->setDocblock(Zend_CodeGenerator_Php_Docblock::fromReflection($docblock));

            $bodyLines = explode("\n", $body);
            $bodyReturn = array();
            for ($lineNum = 1; $lineNum <= count($bodyLines); $lineNum++) {
                if ($lineNum == $docblock->getStartLine()) {
                    $bodyReturn[] = str_replace('?', $class->getName(), self::$_markerDocblock);  //'/* Zend_CodeGenerator_Php_File-ClassMarker: {' . $class->getName() . '} */';
                    $lineNum = $docblock->getEndLine();
                } else {
                    $bodyReturn[] = $bodyLines[$lineNum - 1]; // adjust for index -> line conversion
                }
            }
            $body = implode("\n", $bodyReturn);
            unset($bodyLines, $bodyReturn, $classStartLine, $classEndLine);
        }

        $file->setBody($body);

        return $file;
    }

    /**
     * setDocblock() Set the docblock
     *
     * @param Zend_CodeGenerator_Php_Docblock|array|string $docblock
     * @return Zend_CodeGenerator_Php_File
     */
    public function setDocblock($docblock)
    {
        if (is_string($docblock)) {
            $docblock = array('shortDescription' => $docblock);
        }

        if (is_array($docblock)) {
            $docblock = new Zend_CodeGenerator_Php_Docblock($docblock);
        } elseif (!$docblock instanceof Zend_CodeGenerator_Php_Docblock) {
            #require_once 'Zend/CodeGenerator/Php/Exception.php';
            throw new Zend_CodeGenerator_Php_Exception('setDocblock() is expecting either a string, array or an instance of Zend_CodeGenerator_Php_Docblock');
        }

        $this->_docblock = $docblock;
        return $this;
    }

    /**
     * Get docblock
     *
     * @return Zend_CodeGenerator_Php_Docblock
     */
    public function getDocblock()
    {
        return $this->_docblock;
    }

    /**
     * setRequiredFiles
     *
     * @param array $requiredFiles
     * @return Zend_CodeGenerator_Php_File
     */
    public function setRequiredFiles($requiredFiles)
    {
        $this->_requiredFiles = $requiredFiles;
        return $this;
    }

    /**
     * getRequiredFiles()
     *
     * @return array
     */
    public function getRequiredFiles()
    {
        return $this->_requiredFiles;
    }

    /**
     * setClasses()
     *
     * @param array $classes
     * @return Zend_CodeGenerator_Php_File
     */
    public function setClasses(Array $classes)
    {
        foreach ($classes as $class) {
            $this->setClass($class);
        }
        return $this;
    }

    /**
     * getClass()
     *
     * @param string $name
     * @return Zend_CodeGenerator_Php_Class
     */
    public function getClass($name = null)
    {
        if ($name == null) {
            reset($this->_classes);
            return current($this->_classes);
        }

        return $this->_classes[$name];
    }

    /**
     * setClass()
     *
     * @param Zend_CodeGenerator_Php_Class|array $class
     * @return Zend_CodeGenerator_Php_File
     */
    public function setClass($class)
    {
        if (is_array($class)) {
            $class = new Zend_CodeGenerator_Php_Class($class);
            $className = $class->getName();
        } elseif ($class instanceof Zend_CodeGenerator_Php_Class) {
            $className = $class->getName();
        } else {
            #require_once 'Zend/CodeGenerator/Php/Exception.php';
            throw new Zend_CodeGenerator_Php_Exception('Expecting either an array or an instance of Zend_CodeGenerator_Php_Class');
        }

        // @todo check for dup here

        $this->_classes[$className] = $class;
        return $this;
    }

    /**
     * setFilename()
     *
     * @param string $filename
     * @return Zend_CodeGenerator_Php_File
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;
        return $this;
    }

    /**
     * getFilename()
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * getClasses()
     *
     * @return array Array of Zend_CodeGenerator_Php_Class
     */
    public function getClasses()
    {
        return $this->_classes;
    }

    /**
     * setBody()
     *
     * @param string $body
     * @return Zend_CodeGenerator_Php_File
     */
    public function setBody($body)
    {
        $this->_body = $body;
        return $this;
    }

    /**
     * getBody()
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * isSourceDirty()
     *
     * @return bool
     */
    public function isSourceDirty()
    {
        if (($docblock = $this->getDocblock()) && $docblock->isSourceDirty()) {
            return true;
        }

        foreach ($this->_classes as $class) {
            if ($class->isSourceDirty()) {
                return true;
            }
        }

        return parent::isSourceDirty();
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        if ($this->isSourceDirty() === false) {
            return $this->_sourceContent;
        }

        $output = '';

        // start with the body (if there), or open tag
        if (preg_match('#(?:\s*)<\?php#', $this->getBody()) == false) {
            $output = '<?php' . self::LINE_FEED;
        }

        // if there are markers, put the body into the output
        $body = $this->getBody();
        if (preg_match('#/\* Zend_CodeGenerator_Php_File-(.*?)Marker:#', $body)) {
            $output .= $body;
            $body    = '';
        }

        // Add file docblock, if any
        if (null !== ($docblock = $this->getDocblock())) {
            $docblock->setIndentation('');
            $regex = preg_quote(self::$_markerDocblock, '#');
            if (preg_match('#'.$regex.'#', $output)) {
                $output  = preg_replace('#'.$regex.'#', $docblock->generate(), $output, 1);
            } else {
                $output .= $docblock->generate() . self::LINE_FEED;
            }
        }

        // newline
        $output .= self::LINE_FEED;

        // process required files
        // @todo marker replacement for required files
        $requiredFiles = $this->getRequiredFiles();
        if (!empty($requiredFiles)) {
            foreach ($requiredFiles as $requiredFile) {
                $output .= '#require_once \'' . $requiredFile . '\';' . self::LINE_FEED;
            }

            $output .= self::LINE_FEED;
        }

        // process classes
        $classes = $this->getClasses();
        if (!empty($classes)) {
            foreach ($classes as $class) {
                $regex = str_replace('?', $class->getName(), self::$_markerClass);
                $regex = preg_quote($regex, '#');
                if (preg_match('#'.$regex.'#', $output)) {
                    $output = preg_replace('#'.$regex.'#', $class->generate(), $output, 1);
                } else {
                    $output .= $class->generate() . self::LINE_FEED;
                }
            }

        }

        if (!empty($body)) {

            // add an extra space betwee clsses and
            if (!empty($classes)) {
                $output .= self::LINE_FEED;
            }

            $output .= $body;
        }

        return $output;
    }

    public function write()
    {
        if ($this->_filename == '' || !is_writable(dirname($this->_filename))) {
            #require_once 'Zend/CodeGenerator/Php/Exception.php';
            throw new Zend_CodeGenerator_Php_Exception('This code generator object is not writable.');
        }
        file_put_contents($this->_filename, $this->generate());
        return $this;
    }

}
