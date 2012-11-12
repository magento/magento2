<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Generator;

use Zend\Code\Reflection\FileReflection;

/**
 * @category   Zend
 * @package    Zend_Code_Generator
 */
class FileGenerator extends AbstractGenerator
{

    /**
     * @var string
     */
    protected $filename = null;

    /**
     * @var DocBlockGenerator
     */
    protected $docBlock = null;

    /**
     * @var array
     */
    protected $requiredFiles = array();

    /**
     * @var string
     */
    protected $namespace = null;

    /**
     * @var array
     */
    protected $uses = array();

    /**
     * @var array
     */
    protected $classes = array();

    /**
     * @var string
     */
    protected $body = null;

    /**
     * Constructor
     *
     * Passes $options to {@link setOptions()}.
     *
     * @param array|\Traversable $options
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * fromReflectedFilePath() - use this if you intend on generating code generation objects based on the same file.
     * This will keep previous changes to the file in tact during the same PHP process
     *
     * @param string $filePath
     * @param bool   $includeIfNotAlreadyIncluded
     * @throws Exception\InvalidArgumentException
     * @return FileGenerator
     */
    public static function fromReflectedFileName($filePath, $includeIfNotAlreadyIncluded = true)
    {
        $realpath = realpath($filePath);

        if ($realpath === false) {
            if (($realpath = FileReflection::findRealpathInIncludePath($filePath)) === false) {
                throw new Exception\InvalidArgumentException('No file for ' . $realpath . ' was found.');
            }
        }

        if ($includeIfNotAlreadyIncluded && !in_array($realpath, get_included_files())) {
            include $realpath;
        }

        $codeGenerator = self::fromReflection(($fileReflector = new FileReflection($realpath)));

        return $codeGenerator;
    }

    /**
     * fromReflection()
     *
     * @param FileReflection $fileReflection
     * @return FileGenerator
     */
    public static function fromReflection(FileReflection $fileReflection)
    {
        $file = new self();

        $file->setSourceContent($fileReflection->getContents());
        $file->setSourceDirty(false);

        $body = $fileReflection->getContents();

        foreach ($fileReflection->getClasses() as $class) {
            $phpClass = ClassGenerator::fromReflection($class);
            $phpClass->setContainingFileGenerator($file);

            $file->setClass($phpClass);

            $classStartLine = $class->getStartLine(true);
            $classEndLine   = $class->getEndLine();

            $bodyLines  = explode("\n", $body);
            $bodyReturn = array();
            for ($lineNum = 1; $lineNum <= count($bodyLines); $lineNum++) {
                if ($lineNum == $classStartLine) {

                    $bodyReturn[] = str_replace(
                        '?',
                        $class->getName(),
                        '/* Zend_Code_Generator_Php_File-ClassMarker: {?} */'
                    );

                    $lineNum = $classEndLine;
                } else {
                    $bodyReturn[] = $bodyLines[$lineNum - 1]; // adjust for index -> line conversion
                }
            }
            $body = implode("\n", $bodyReturn);
            unset($bodyLines, $bodyReturn, $classStartLine, $classEndLine);
        }

        $namespace = $fileReflection->getNamespace();

        if ($namespace != '') {
            $file->setNamespace($namespace);
        }

        $uses = $fileReflection->getUses();
        if ($uses) {
            $file->setUses($uses);
        }

        if (($fileReflection->getDocComment() != '')) {
            $docBlock = $fileReflection->getDocBlock();
            $file->setDocBlock(DocBlockGenerator::fromReflection($docBlock));

            $bodyLines  = explode("\n", $body);
            $bodyReturn = array();
            for ($lineNum = 1; $lineNum <= count($bodyLines); $lineNum++) {
                if ($lineNum == $docBlock->getStartLine()) {
                    $bodyReturn[] = str_replace(
                        '?',
                        $class->getName(),
                        '/* Zend_Code_Generator_FileGenerator-DocBlockMarker */'
                    );
                    $lineNum      = $docBlock->getEndLine();
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

    public static function fromArray(array $values)
    {
        $fileGenerator = new static;
        foreach ($values as $name => $value) {
            switch ($name) {
                case 'filename':
                    $fileGenerator->filename = $value;
                    continue;
                case 'class':
                    $fileGenerator->setClass(($value instanceof ClassGenerator) ? : ClassGenerator::fromArray($value));
                    continue;
                case 'requiredFiles':
                case 'required_files':
                    $fileGenerator->setRequiredFiles($value);
                    continue;
                default:
                    if (property_exists($fileGenerator, $name)) {
                        $fileGenerator->{$name} = $value;
                    } elseif (method_exists($fileGenerator, 'set' . $name)) {
                        $fileGenerator->{'set' . $name}($value);
                    }
            }
        }
        return $fileGenerator;
    }


    /**
     * setDocBlock() Set the DocBlock
     *
     * @param DocBlockGenerator|string $docBlock
     * @throws Exception\InvalidArgumentException
     * @return FileGenerator
     */
    public function setDocBlock($docBlock)
    {
        if (is_string($docBlock)) {
            $docBlock = array('shortDescription' => $docBlock);
        }

        if (is_array($docBlock)) {
            $docBlock = new DocBlockGenerator($docBlock);
        } elseif (!$docBlock instanceof DocBlockGenerator) {
            throw new Exception\InvalidArgumentException(
                'setDocBlock() is expecting either a string, array or an instance of Zend\Code\Generator\DocBlockGenerator'
            );
        }

        $this->docBlock = $docBlock;
        return $this;
    }

    /**
     * Get DocBlock
     *
     * @return DocBlockGenerator
     */
    public function getDocBlock()
    {
        return $this->docBlock;
    }

    /**
     * setRequiredFiles
     *
     * @param array $requiredFiles
     * @return FileGenerator
     */
    public function setRequiredFiles(array $requiredFiles)
    {
        $this->requiredFiles = $requiredFiles;
        return $this;
    }

    /**
     * getRequiredFiles()
     *
     * @return array
     */
    public function getRequiredFiles()
    {
        return $this->requiredFiles;
    }

    /**
     * setClasses()
     *
     * @param array $classes
     * @return FileGenerator
     */
    public function setClasses(array $classes)
    {
        foreach ($classes as $class) {
            $this->setClass($class);
        }
        return $this;
    }

    /**
     * getNamespace()
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * setNamespace()
     *
     * @param $namespace
     * @return FileGenerator
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * getUses()
     *
     * Returns an array with the first element the use statement, second is the as part.
     * If $withResolvedAs is set to true, there will be a third element that is the
     * "resolved" as statement, as the second part is not required in use statements
     *
     * @param bool $withResolvedAs
     * @return array
     */
    public function getUses($withResolvedAs = false)
    {
        $uses = $this->uses;
        if ($withResolvedAs) {
            for ($useIndex = 0; $useIndex < count($uses); $useIndex++) {
                if ($uses[$useIndex][1] == '') {
                    if (($lastSeparator = strrpos($uses[$useIndex][0], '\\')) !== false) {
                        $uses[$useIndex][2] = substr($uses[$useIndex][0], $lastSeparator + 1);
                    } else {
                        $uses[$useIndex][2] = $uses[$useIndex][0];
                    }
                } else {
                    $uses[$useIndex][2] = $uses[$useIndex][1];
                }
            }
        }
        return $uses;
    }

    /**
     * setUses()
     *
     * @param array $uses
     * @return FileGenerator
     */
    public function setUses(array $uses)
    {
        foreach ($uses as $use) {
            $this->setUse($use[0], $use[1]);
        }
        return $this;
    }

    /**
     * setUse()
     *
     * @param string $use
     * @param string $as
     * @return FileGenerator
     */
    public function setUse($use, $as = null)
    {
        $this->uses[] = array($use, $as);
        return $this;
    }

    /**
     * getClass()
     *
     * @param string $name
     * @return ClassGenerator
     */
    public function getClass($name = null)
    {
        if ($name == null) {
            reset($this->classes);
            return current($this->classes);
        }

        return $this->classes[$name];
    }

    /**
     * setClass()
     *
     * @param  array|string|ClassGenerator $class
     * @throws Exception\InvalidArgumentException
     * @return FileGenerator
     */
    public function setClass($class)
    {
        if (is_array($class)) {
            $class = ClassGenerator::fromArray($class);
        } elseif (is_string($class)) {
            $class = new ClassGenerator($class);
        } elseif (!$class instanceof ClassGenerator) {
            throw new Exception\InvalidArgumentException(
                'setClass() is expecting either a string, array or an instance of Zend\Code\Generator\ClassGenerator'
            );
        }

        // @todo check for dup here
        $className                 = $class->getName();
        $this->classes[$className] = $class;
        return $this;
    }

    /**
     * setFilename()
     *
     * @param string $filename
     * @return FileGenerator
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * getFilename()
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * getClasses()
     *
     * @return ClassGenerator[] Array of ClassGenerators
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * setBody()
     *
     * @param string $body
     * @return FileGenerator
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * getBody()
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * isSourceDirty()
     *
     * @return bool
     */
    public function isSourceDirty()
    {
        if (($docBlock = $this->getDocBlock()) && $docBlock->isSourceDirty()) {
            return true;
        }

        foreach ($this->classes as $class) {
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
            return $this->sourceContent;
        }

        $output = '';

        // start with the body (if there), or open tag
        $body = $this->getBody();
        if (preg_match('#(?:\s*)<\?php#', $body) == false) {
            $output = '<?php' . self::LINE_FEED;
        }

        // if there are markers, put the body into the output
        if (preg_match('#/\* Zend_Code_Generator_FileGenerator-(.*?)Marker:#', $body)) {
            $tokens = token_get_all($body);
            foreach ($tokens as $token) {
                if (is_array($token) && in_array($token[0], array(T_OPEN_TAG, T_COMMENT, T_DOC_COMMENT, T_WHITESPACE))
                ) {
                    $output .= $token[1];
                }
            }
            $body = '';
        }

        // Add file DocBlock, if any
        if (null !== ($docBlock = $this->getDocBlock())) {
            $docBlock->setIndentation('');

            if (preg_match('#/* Zend_Code_Generator_FileGenerator-DocBlockMarker */#', $output)) {
                $output = preg_replace('#/* Zend_CodeGenerator_Php_File-DocBlockMarker */#', $docBlock->generate(),
                                       $output, 1);
            } else {
                $output .= $docBlock->generate() . self::LINE_FEED;
            }
        }

        // newline
        $output .= self::LINE_FEED;

        // namespace, if any
        $namespace = $this->getNamespace();
        if ($namespace) {
            $output .= sprintf('namespace %s;%s', $namespace, str_repeat(self::LINE_FEED, 2));
        }

        // process required files
        // @todo marker replacement for required files
        $requiredFiles = $this->getRequiredFiles();
        if (!empty($requiredFiles)) {
            foreach ($requiredFiles as $requiredFile) {
                $output .= 'require_once \'' . $requiredFile . '\';' . self::LINE_FEED;
            }

            $output .= self::LINE_FEED;
        }

        // process import statements
        $uses = $this->getUses();
        if (!empty($uses)) {
            foreach ($uses as $use) {
                list($import, $alias) = $use;
                if (null === $alias) {
                    $output .= sprintf('use %s;%s', $import, self::LINE_FEED);
                } else {
                    $output .= sprintf('use %s as %s;%s', $import, $alias, self::LINE_FEED);
                }
            }
            $output .= self::LINE_FEED;
        }

        // process classes
        $classes = $this->getClasses();
        if (!empty($classes)) {
            foreach ($classes as $class) {
                $regex = str_replace('?', $class->getName(),
                                     '/* Zend_Code_Generator_FileGenerator-ClassMarker: {?} */');
                $regex = preg_quote($regex, '#');
                if (preg_match('#' . $regex . '#', $output)) {
                    $output = preg_replace('#' . $regex . '#', $class->generate(), $output, 1);
                } else {
                    if ($namespace) {
                        $class->setNamespaceName(null);
                    }
                    $output .= $class->generate() . self::LINE_FEED;
                }
            }

        }

        if (!empty($body)) {

            // add an extra space between classes and
            if (!empty($classes)) {
                $output .= self::LINE_FEED;
            }

            $output .= $body;
        }

        return $output;
    }

    public function write()
    {
        if ($this->filename == '' || !is_writable(dirname($this->filename))) {
            throw new Exception\RuntimeException('This code generator object is not writable.');
        }
        file_put_contents($this->filename, $this->generate());
        return $this;
    }

}
