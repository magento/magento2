<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Resolver;

use SplFileInfo;
use Traversable;
use Zend\Stdlib\SplStack;
use Zend\View\Exception;
use Zend\View\Renderer\RendererInterface as Renderer;

/**
 * Resolves view scripts based on a stack of paths
 */
class TemplatePathStack implements ResolverInterface
{
    const FAILURE_NO_PATHS  = 'TemplatePathStack_Failure_No_Paths';
    const FAILURE_NOT_FOUND = 'TemplatePathStack_Failure_Not_Found';

    /**
     * Default suffix to use
     *
     * Appends this suffix if the template requested does not use it.
     *
     * @var string
     */
    protected $defaultSuffix = 'phtml';

    /**
     * @var SplStack
     */
    protected $paths;

    /**
     * Reason for last lookup failure
     *
     * @var false|string
     */
    protected $lastLookupFailure = false;

    /**
     * Flag indicating whether or not LFI protection for rendering view scripts is enabled
     * @var bool
     */
    protected $lfiProtectionOn = true;

    /**@+
     * Flags used to determine if a stream wrapper should be used for enabling short tags
     * @var bool
     */
    protected $useViewStream    = false;
    protected $useStreamWrapper = false;
    /**@-*/

    /**
     * Constructor
     *
     * @param  null|array|Traversable $options
     */
    public function __construct($options = null)
    {
        $this->useViewStream = (bool) ini_get('short_open_tag');
        if ($this->useViewStream) {
            if (!in_array('zend.view', stream_get_wrappers())) {
                stream_wrapper_register('zend.view', 'Zend\View\Stream');
            }
        }

        $this->paths = new SplStack;
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Configure object
     *
     * @param  array|Traversable $options
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expected array or Traversable object; received "%s"',
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'lfi_protection':
                    $this->setLfiProtection($value);
                    break;
                case 'script_paths':
                    $this->addPaths($value);
                    break;
                case 'use_stream_wrapper':
                    $this->setUseStreamWrapper($value);
                    break;
                case 'default_suffix':
                    $this->setDefaultSuffix($value);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Set default file suffix
     *
     * @param  string $defaultSuffix
     * @return TemplatePathStack
     */
    public function setDefaultSuffix($defaultSuffix)
    {
        $this->defaultSuffix = (string) $defaultSuffix;
        $this->defaultSuffix = ltrim($this->defaultSuffix, '.');
        return $this;
    }

    /**
     * Get default file suffix
     *
     * @return string
     */
    public function getDefaultSuffix()
    {
        return $this->defaultSuffix;
    }

    /**
     * Add many paths to the stack at once
     *
     * @param  array $paths
     * @return TemplatePathStack
     */
    public function addPaths(array $paths)
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
        return $this;
    }

    /**
     * Rest the path stack to the paths provided
     *
     * @param  SplStack|array $paths
     * @return TemplatePathStack
     * @throws Exception\InvalidArgumentException
     */
    public function setPaths($paths)
    {
        if ($paths instanceof SplStack) {
            $this->paths = $paths;
        } elseif (is_array($paths)) {
            $this->clearPaths();
            $this->addPaths($paths);
        } else {
            throw new Exception\InvalidArgumentException(
                "Invalid argument provided for \$paths, expecting either an array or SplStack object"
            );
        }

        return $this;
    }

    /**
     * Normalize a path for insertion in the stack
     *
     * @param  string $path
     * @return string
     */
    public static function normalizePath($path)
    {
        $path = rtrim($path, '/');
        $path = rtrim($path, '\\');
        $path .= DIRECTORY_SEPARATOR;
        return $path;
    }

    /**
     * Add a single path to the stack
     *
     * @param  string $path
     * @return TemplatePathStack
     * @throws Exception\InvalidArgumentException
     */
    public function addPath($path)
    {
        if (!is_string($path)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid path provided; must be a string, received %s',
                gettype($path)
            ));
        }
        $this->paths[] = static::normalizePath($path);
        return $this;
    }

    /**
     * Clear all paths
     *
     * @return void
     */
    public function clearPaths()
    {
        $this->paths = new SplStack;
    }

    /**
     * Returns stack of paths
     *
     * @return SplStack
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Set LFI protection flag
     *
     * @param  bool $flag
     * @return TemplatePathStack
     */
    public function setLfiProtection($flag)
    {
        $this->lfiProtectionOn = (bool) $flag;
        return $this;
    }

    /**
     * Return status of LFI protection flag
     *
     * @return bool
     */
    public function isLfiProtectionOn()
    {
        return $this->lfiProtectionOn;
    }

    /**
     * Set flag indicating if stream wrapper should be used if short_open_tag is off
     *
     * @param  bool $flag
     * @return TemplatePathStack
     */
    public function setUseStreamWrapper($flag)
    {
        $this->useStreamWrapper = (bool) $flag;
        return $this;
    }

    /**
     * Should the stream wrapper be used if short_open_tag is off?
     *
     * Returns true if the use_stream_wrapper flag is set, and if short_open_tag
     * is disabled.
     *
     * @return bool
     */
    public function useStreamWrapper()
    {
        return ($this->useViewStream && $this->useStreamWrapper);
    }

    /**
     * Retrieve the filesystem path to a view script
     *
     * @param  string $name
     * @param  null|Renderer $renderer
     * @return string
     * @throws Exception\DomainException
     */
    public function resolve($name, Renderer $renderer = null)
    {
        $this->lastLookupFailure = false;

        if ($this->isLfiProtectionOn() && preg_match('#\.\.[\\\/]#', $name)) {
            throw new Exception\DomainException(
                'Requested scripts may not include parent directory traversal ("../", "..\\" notation)'
            );
        }

        if (!count($this->paths)) {
            $this->lastLookupFailure = static::FAILURE_NO_PATHS;
            return false;
        }

        // Ensure we have the expected file extension
        $defaultSuffix = $this->getDefaultSuffix();
        if (pathinfo($name, PATHINFO_EXTENSION) == '') {
            $name .= '.' . $defaultSuffix;
        }

        foreach ($this->paths as $path) {
            $file = new SplFileInfo($path . $name);
            if ($file->isReadable()) {
                // Found! Return it.
                if (($filePath = $file->getRealPath()) === false && substr($path, 0, 7) === 'phar://') {
                    // Do not try to expand phar paths (realpath + phars == fail)
                    $filePath = $path . $name;
                    if (!file_exists($filePath)) {
                        break;
                    }
                }
                if ($this->useStreamWrapper()) {
                    // If using a stream wrapper, prepend the spec to the path
                    $filePath = 'zend.view://' . $filePath;
                }
                return $filePath;
            }
        }

        $this->lastLookupFailure = static::FAILURE_NOT_FOUND;
        return false;
    }

    /**
     * Get the last lookup failure message, if any
     *
     * @return false|string
     */
    public function getLastLookupFailure()
    {
        return $this->lastLookupFailure;
    }
}
