<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use stdClass;
use Zend\View;
use Zend\View\Exception;

/**
 * Helper for setting and retrieving script elements for HTML head section
 *
 * Allows the following method calls:
 * @method HeadScript appendFile($src, $type = 'text/javascript', $attrs = array())
 * @method HeadScript offsetSetFile($index, $src, $type = 'text/javascript', $attrs = array())
 * @method HeadScript prependFile($src, $type = 'text/javascript', $attrs = array())
 * @method HeadScript setFile($src, $type = 'text/javascript', $attrs = array())
 * @method HeadScript appendScript($script, $type = 'text/javascript', $attrs = array())
 * @method HeadScript offsetSetScript($index, $src, $type = 'text/javascript', $attrs = array())
 * @method HeadScript prependScript($script, $type = 'text/javascript', $attrs = array())
 * @method HeadScript setScript($script, $type = 'text/javascript', $attrs = array())
 */
class HeadScript extends Placeholder\Container\AbstractStandalone
{
    /**
     * Script type constants
     *
     * @const string
     */
    const FILE   = 'FILE';
    const SCRIPT = 'SCRIPT';

    /**
     * Registry key for placeholder
     *
     * @var string
     */
    protected $regKey = 'Zend_View_Helper_HeadScript';

    /**
     * Are arbitrary attributes allowed?
     *
     * @var bool
     */
    protected $arbitraryAttributes = false;

    /**
     * Is capture lock?
     *
     * @var bool
     */
    protected $captureLock;

    /**
     * Capture type
     *
     * @var string
     */
    protected $captureScriptType;

    /**
     * Capture attributes
     *
     * @var null|array
     */
    protected $captureScriptAttrs = null;

    /**
     * Capture type (append, prepend, set)
     *
     * @var string
     */
    protected $captureType;

    /**
     * Optional allowed attributes for script tag
     *
     * @var array
     */
    protected $optionalAttributes = array(
        'charset',
        'crossorigin',
        'defer',
        'language',
        'src',
    );

    /**
     * Required attributes for script tag
     *
     * @var string
     */
    protected $requiredAttributes = array('type');

    /**
     * Whether or not to format scripts using CDATA; used only if doctype
     * helper is not accessible
     *
     * @var bool
     */
    public $useCdata = false;

    /**
     * Constructor
     *
     * Set separator to PHP_EOL.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setSeparator(PHP_EOL);
    }

    /**
     * Return headScript object
     *
     * Returns headScript helper object; optionally, allows specifying a script
     * or script file to include.
     *
     * @param  string $mode      Script or file
     * @param  string $spec      Script/url
     * @param  string $placement Append, prepend, or set
     * @param  array  $attrs     Array of script attributes
     * @param  string $type      Script type and/or array of script attributes
     * @return HeadScript
     */
    public function __invoke(
        $mode = self::FILE,
        $spec = null,
        $placement = 'APPEND',
        array $attrs = array(),
        $type = 'text/javascript'
    ) {
        if ((null !== $spec) && is_string($spec)) {
            $action    = ucfirst(strtolower($mode));
            $placement = strtolower($placement);
            switch ($placement) {
                case 'set':
                case 'prepend':
                case 'append':
                    $action = $placement . $action;
                    break;
                default:
                    $action = 'append' . $action;
                    break;
            }
            $this->$action($spec, $type, $attrs);
        }

        return $this;
    }

    /**
     * Overload method access
     *
     * @param  string $method Method to call
     * @param  array  $args   Arguments of method
     * @throws Exception\BadMethodCallException if too few arguments or invalid method
     * @return HeadScript
     */
    public function __call($method, $args)
    {
        if (preg_match('/^(?P<action>set|(ap|pre)pend|offsetSet)(?P<mode>File|Script)$/', $method, $matches)) {
            if (1 > count($args)) {
                throw new Exception\BadMethodCallException(sprintf(
                    'Method "%s" requires at least one argument',
                    $method
                ));
            }

            $action  = $matches['action'];
            $mode    = strtolower($matches['mode']);
            $type    = 'text/javascript';
            $attrs   = array();

            if ('offsetSet' == $action) {
                $index = array_shift($args);
                if (1 > count($args)) {
                    throw new Exception\BadMethodCallException(sprintf(
                        'Method "%s" requires at least two arguments, an index and source',
                        $method
                    ));
                }
            }

            $content = $args[0];

            if (isset($args[1])) {
                $type = (string) $args[1];
            }
            if (isset($args[2])) {
                $attrs = (array) $args[2];
            }

            switch ($mode) {
                case 'script':
                    $item = $this->createData($type, $attrs, $content);
                    if ('offsetSet' == $action) {
                        $this->offsetSet($index, $item);
                    } else {
                        $this->$action($item);
                    }
                    break;
                case 'file':
                default:
                    if (!$this->isDuplicate($content)) {
                        $attrs['src'] = $content;
                        $item = $this->createData($type, $attrs);
                        if ('offsetSet' == $action) {
                            $this->offsetSet($index, $item);
                        } else {
                            $this->$action($item);
                        }
                    }
                    break;
            }

            return $this;
        }

        return parent::__call($method, $args);
    }

    /**
     * Retrieve string representation
     *
     * @param  string|int $indent Amount of whitespaces or string to use for indention
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
            ? $this->getWhitespace($indent)
            : $this->getIndent();

        if ($this->view) {
            $useCdata = $this->view->plugin('doctype')->isXhtml();
        } else {
            $useCdata = $this->useCdata;
        }

        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd   = ($useCdata) ? '//]]>' : '//-->';

        $items = array();
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->isValid($item)) {
                continue;
            }

            $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
        }

        return implode($this->getSeparator(), $items);
    }

    /**
     * Start capture action
     *
     * @param  mixed  $captureType Type of capture
     * @param  string $type        Type of script
     * @param  array  $attrs       Attributes of capture
     * @throws Exception\RuntimeException
     * @return void
     */
    public function captureStart(
        $captureType = Placeholder\Container\AbstractContainer::APPEND,
        $type = 'text/javascript',
        $attrs = array()
    ) {
        if ($this->captureLock) {
            throw new Exception\RuntimeException('Cannot nest headScript captures');
        }

        $this->captureLock        = true;
        $this->captureType        = $captureType;
        $this->captureScriptType  = $type;
        $this->captureScriptAttrs = $attrs;
        ob_start();
    }

    /**
     * End capture action and store
     *
     * @return void
     */
    public function captureEnd()
    {
        $content                  = ob_get_clean();
        $type                     = $this->captureScriptType;
        $attrs                    = $this->captureScriptAttrs;
        $this->captureScriptType  = null;
        $this->captureScriptAttrs = null;
        $this->captureLock        = false;

        switch ($this->captureType) {
            case Placeholder\Container\AbstractContainer::SET:
            case Placeholder\Container\AbstractContainer::PREPEND:
            case Placeholder\Container\AbstractContainer::APPEND:
                $action = strtolower($this->captureType) . 'Script';
                break;
            default:
                $action = 'appendScript';
                break;
        }

        $this->$action($content, $type, $attrs);
    }

    /**
     * Create data item containing all necessary components of script
     *
     * @param  string $type       Type of data
     * @param  array  $attributes Attributes of data
     * @param  string $content    Content of data
     * @return stdClass
     */
    public function createData($type, array $attributes, $content = null)
    {
        $data             = new stdClass();
        $data->type       = $type;
        $data->attributes = $attributes;
        $data->source     = $content;

        return $data;
    }

    /**
     * Is the file specified a duplicate?
     *
     * @param  string $file Name of file to check
     * @return bool
     */
    protected function isDuplicate($file)
    {
        foreach ($this->getContainer() as $item) {
            if (($item->source === null)
                && array_key_exists('src', $item->attributes)
                && ($file == $item->attributes['src'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is the script provided valid?
     *
     * @param  mixed  $value  Is the given script valid?
     * @return bool
     */
    protected function isValid($value)
    {
        if ((!$value instanceof stdClass)
            || !isset($value->type)
            || (!isset($value->source)
                && !isset($value->attributes))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Create script HTML
     *
     * @param  mixed  $item        Item to convert
     * @param  string $indent      String to add before the item
     * @param  string $escapeStart Starting sequence
     * @param  string $escapeEnd   Ending sequence
     * @return string
     */
    public function itemToString($item, $indent, $escapeStart, $escapeEnd)
    {
        $attrString = '';
        if (!empty($item->attributes)) {
            foreach ($item->attributes as $key => $value) {
                if ((!$this->arbitraryAttributesAllowed() && !in_array($key, $this->optionalAttributes))
                    || in_array($key, array('conditional', 'noescape'))) {
                    continue;
                }
                if ('defer' == $key) {
                    $value = 'defer';
                }
                $attrString .= sprintf(' %s="%s"', $key, ($this->autoEscape) ? $this->escape($value) : $value);
            }
        }

        $addScriptEscape = !(isset($item->attributes['noescape'])
            && filter_var($item->attributes['noescape'], FILTER_VALIDATE_BOOLEAN));

        $type = ($this->autoEscape) ? $this->escape($item->type) : $item->type;
        $html = '<script type="' . $type . '"' . $attrString . '>';
        if (!empty($item->source)) {
            $html .= PHP_EOL;

            if ($addScriptEscape) {
                $html .= $indent . '    ' . $escapeStart . PHP_EOL;
            }

            $html .= $indent . '    ' . $item->source;

            if ($addScriptEscape) {
                $html .= PHP_EOL . $indent . '    ' . $escapeEnd;
            }

            $html .= PHP_EOL . $indent;
        }
        $html .= '</script>';

        if (isset($item->attributes['conditional'])
            && !empty($item->attributes['conditional'])
            && is_string($item->attributes['conditional'])
        ) {
            // inner wrap with comment end and start if !IE
            if (str_replace(' ', '', $item->attributes['conditional']) === '!IE') {
                $html = '<!-->' . $html . '<!--';
            }
            $html = $indent . '<!--[if ' . $item->attributes['conditional'] . ']>' . $html . '<![endif]-->';
        } else {
            $html = $indent . $html;
        }

        return $html;
    }

    /**
     * Override append
     *
     * @param  string $value Append script or file
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function append($value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument passed to append(); '
                . 'please use one of the helper methods, appendScript() or appendFile()'
            );
        }

        return $this->getContainer()->append($value);
    }

    /**
     * Override prepend
     *
     * @param  string $value Prepend script or file
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function prepend($value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument passed to prepend(); '
                . 'please use one of the helper methods, prependScript() or prependFile()'
            );
        }

        return $this->getContainer()->prepend($value);
    }

    /**
     * Override set
     *
     * @param  string $value Set script or file
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function set($value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument passed to set(); please use one of the helper methods, setScript() or setFile()'
            );
        }

        return $this->getContainer()->set($value);
    }

    /**
     * Override offsetSet
     *
     * @param  string|int $index Set script of file offset
     * @param  mixed      $value
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function offsetSet($index, $value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument passed to offsetSet(); '
                . 'please use one of the helper methods, offsetSetScript() or offsetSetFile()'
            );
        }

        return $this->getContainer()->offsetSet($index, $value);
    }

    /**
     * Set flag indicating if arbitrary attributes are allowed
     *
     * @param  bool $flag Set flag
     * @return HeadScript
     */
    public function setAllowArbitraryAttributes($flag)
    {
        $this->arbitraryAttributes = (bool) $flag;
        return $this;
    }

    /**
     * Are arbitrary attributes allowed?
     *
     * @return bool
     */
    public function arbitraryAttributesAllowed()
    {
        return $this->arbitraryAttributes;
    }
}
