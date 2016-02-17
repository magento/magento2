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
 * Helper for setting and retrieving stylesheets
 *
 * Allows the following method calls:
 * @method HeadStyle appendStyle($content, $attributes = array())
 * @method HeadStyle offsetSetStyle($index, $content, $attributes = array())
 * @method HeadStyle prependStyle($content, $attributes = array())
 * @method HeadStyle setStyle($content, $attributes = array())
 */
class HeadStyle extends Placeholder\Container\AbstractStandalone
{
    /**
     * Registry key for placeholder
     *
     * @var string
     */
    protected $regKey = 'Zend_View_Helper_HeadStyle';

    /**
     * Allowed optional attributes
     *
     * @var array
     */
    protected $optionalAttributes = array('lang', 'title', 'media', 'dir');

    /**
     * Allowed media types
     *
     * @var array
     */
    protected $mediaTypes = array(
        'all', 'aural', 'braille', 'handheld', 'print',
        'projection', 'screen', 'tty', 'tv'
    );

    /**
     * Capture type and/or attributes (used for hinting during capture)
     *
     * @var string
     */
    protected $captureAttrs = null;

    /**
     * Capture lock
     *
     * @var bool
     */
    protected $captureLock;

    /**
     * Capture type (append, prepend, set)
     *
     * @var string
     */
    protected $captureType;

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
     * Return headStyle object
     *
     * Returns headStyle helper object; optionally, allows specifying
     *
     * @param  string       $content    Stylesheet contents
     * @param  string       $placement  Append, prepend, or set
     * @param  string|array $attributes Optional attributes to utilize
     * @return HeadStyle
     */
    public function __invoke($content = null, $placement = 'APPEND', $attributes = array())
    {
        if ((null !== $content) && is_string($content)) {
            switch (strtoupper($placement)) {
                case 'SET':
                    $action = 'setStyle';
                    break;
                case 'PREPEND':
                    $action = 'prependStyle';
                    break;
                case 'APPEND':
                default:
                    $action = 'appendStyle';
                    break;
            }
            $this->$action($content, $attributes);
        }

        return $this;
    }

    /**
     * Overload method calls
     *
     * @param  string $method
     * @param  array  $args
     * @throws Exception\BadMethodCallException When no $content provided or invalid method
     * @return void
     */
    public function __call($method, $args)
    {
        if (preg_match('/^(?P<action>set|(ap|pre)pend|offsetSet)(Style)$/', $method, $matches)) {
            $index  = null;
            $argc   = count($args);
            $action = $matches['action'];

            if ('offsetSet' == $action) {
                if (0 < $argc) {
                    $index = array_shift($args);
                    --$argc;
                }
            }

            if (1 > $argc) {
                throw new Exception\BadMethodCallException(sprintf(
                    'Method "%s" requires minimally content for the stylesheet',
                    $method
                ));
            }

            $content = $args[0];
            $attrs   = array();
            if (isset($args[1])) {
                $attrs = (array) $args[1];
            }

            $item = $this->createData($content, $attrs);

            if ('offsetSet' == $action) {
                $this->offsetSet($index, $item);
            } else {
                $this->$action($item);
            }

            return $this;
        }

        return parent::__call($method, $args);
    }

    /**
     * Create string representation of placeholder
     *
     * @param  string|int $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
            ? $this->getWhitespace($indent)
            : $this->getIndent();

        $items = array();
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->isValid($item)) {
                continue;
            }
            $items[] = $this->itemToString($item, $indent);
        }

        $return = $indent . implode($this->getSeparator() . $indent, $items);
        $return = preg_replace("/(\r\n?|\n)/", '$1' . $indent, $return);

        return $return;
    }

    /**
     * Start capture action
     *
     * @param  string $type
     * @param  string $attrs
     * @throws Exception\RuntimeException
     * @return void
     */
    public function captureStart($type = Placeholder\Container\AbstractContainer::APPEND, $attrs = null)
    {
        if ($this->captureLock) {
            throw new Exception\RuntimeException('Cannot nest headStyle captures');
        }

        $this->captureLock        = true;
        $this->captureAttrs       = $attrs;
        $this->captureType        = $type;
        ob_start();
    }

    /**
     * End capture action and store
     *
     * @return void
     */
    public function captureEnd()
    {
        $content             = ob_get_clean();
        $attrs               = $this->captureAttrs;
        $this->captureAttrs = null;
        $this->captureLock  = false;

        switch ($this->captureType) {
            case Placeholder\Container\AbstractContainer::SET:
                $this->setStyle($content, $attrs);
                break;
            case Placeholder\Container\AbstractContainer::PREPEND:
                $this->prependStyle($content, $attrs);
                break;
            case Placeholder\Container\AbstractContainer::APPEND:
            default:
                $this->appendStyle($content, $attrs);
                break;
        }
    }

    /**
     * Create data item for use in stack
     *
     * @param  string $content
     * @param  array  $attributes
     * @return stdClass
     */
    public function createData($content, array $attributes)
    {
        if (!isset($attributes['media'])) {
            $attributes['media'] = 'screen';
        } elseif (is_array($attributes['media'])) {
            $attributes['media'] = implode(',', $attributes['media']);
        }

        $data = new stdClass();
        $data->content    = $content;
        $data->attributes = $attributes;

        return $data;
    }

    /**
     * Determine if a value is a valid style tag
     *
     * @param  mixed $value
     * @return bool
     */
    protected function isValid($value)
    {
        if ((!$value instanceof stdClass) || !isset($value->content) || !isset($value->attributes)) {
            return false;
        }

        return true;
    }

    /**
     * Convert content and attributes into valid style tag
     *
     * @param  stdClass $item   Item to render
     * @param  string   $indent Indentation to use
     * @return string
     */
    public function itemToString(stdClass $item, $indent)
    {
        $attrString = '';
        if (!empty($item->attributes)) {
            $enc = 'UTF-8';
            if ($this->view instanceof View\Renderer\RendererInterface
                && method_exists($this->view, 'getEncoding')
            ) {
                $enc = $this->view->getEncoding();
            }
            $escaper = $this->getEscaper($enc);
            foreach ($item->attributes as $key => $value) {
                if (!in_array($key, $this->optionalAttributes)) {
                    continue;
                }
                if ('media' == $key) {
                    if (false === strpos($value, ',')) {
                        if (!in_array($value, $this->mediaTypes)) {
                            continue;
                        }
                    } else {
                        $mediaTypes = explode(',', $value);
                        $value = '';
                        foreach ($mediaTypes as $type) {
                            $type = trim($type);
                            if (!in_array($type, $this->mediaTypes)) {
                                continue;
                            }
                            $value .= $type .',';
                        }
                        $value = substr($value, 0, -1);
                    }
                }
                $attrString .= sprintf(' %s="%s"', $key, $escaper->escapeHtmlAttr($value));
            }
        }

        $escapeStart = $indent . '<!--' . PHP_EOL;
        $escapeEnd = $indent . '-->' . PHP_EOL;
        if (isset($item->attributes['conditional'])
            && !empty($item->attributes['conditional'])
            && is_string($item->attributes['conditional'])
        ) {
            $escapeStart = null;
            $escapeEnd = null;
        }

        $html = '<style type="text/css"' . $attrString . '>' . PHP_EOL
            . $escapeStart . $indent . $item->content . PHP_EOL . $escapeEnd
            . '</style>';

        if (null == $escapeStart && null == $escapeEnd) {
            // inner wrap with comment end and start if !IE
            if (str_replace(' ', '', $item->attributes['conditional']) === '!IE') {
                $html = '<!-->' . $html . '<!--';
            }
            $html = '<!--[if ' . $item->attributes['conditional'] . ']>' . $html . '<![endif]-->';
        }

        return $html;
    }

    /**
     * Override append to enforce style creation
     *
     * @param  mixed $value
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function append($value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'Invalid value passed to append; please use appendStyle()'
            );
        }

        return $this->getContainer()->append($value);
    }

    /**
     * Override offsetSet to enforce style creation
     *
     * @param  string|int $index
     * @param  mixed      $value
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function offsetSet($index, $value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'Invalid value passed to offsetSet; please use offsetSetStyle()'
            );
        }

        return $this->getContainer()->offsetSet($index, $value);
    }

    /**
     * Override prepend to enforce style creation
     *
     * @param  mixed $value
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function prepend($value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'Invalid value passed to prepend; please use prependStyle()'
            );
        }

        return $this->getContainer()->prepend($value);
    }

    /**
     * Override set to enforce style creation
     *
     * @param  mixed $value
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function set($value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException('Invalid value passed to set; please use setStyle()');
        }

        return $this->getContainer()->set($value);
    }
}
