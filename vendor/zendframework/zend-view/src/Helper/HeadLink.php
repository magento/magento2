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
 * Zend_Layout_View_Helper_HeadLink
 *
 * @see http://www.w3.org/TR/xhtml1/dtds.html
 *
 * Creates the following virtual methods:
 * @method HeadLink appendStylesheet($href, $media = 'screen', $conditionalStylesheet = '', $extras = array())
 * @method HeadLink offsetSetStylesheet($index, $href, $media = 'screen', $conditionalStylesheet = '', $extras = array())
 * @method HeadLink prependStylesheet($href, $media = 'screen', $conditionalStylesheet = '', $extras = array())
 * @method HeadLink setStylesheet($href, $media = 'screen', $conditionalStylesheet = '', $extras = array())
 * @method HeadLink appendAlternate($href, $type, $title, $extras = array())
 * @method HeadLink offsetSetAlternate($index, $href, $type, $title, $extras = array())
 * @method HeadLink prependAlternate($href, $type, $title, $extras = array())
 * @method HeadLink setAlternate($href, $type, $title, $extras = array())
 */
class HeadLink extends Placeholder\Container\AbstractStandalone
{
    /**
     * Allowed attributes
     *
     * @var string[]
     */
    protected $itemKeys = array(
        'charset',
        'href',
        'hreflang',
        'id',
        'media',
        'rel',
        'rev',
        'sizes',
        'type',
        'title',
        'extras'
    );

    /**
     * Registry key for placeholder
     *
     * @var string
     */
    protected $regKey = 'Zend_View_Helper_HeadLink';

    /**
     * Constructor
     *
     * Use PHP_EOL as separator
     */
    public function __construct()
    {
        parent::__construct();

        $this->setSeparator(PHP_EOL);
    }

    /**
     * Proxy to __invoke()
     *
     * Allows calling $helper->headLink(), but, more importantly, chaining calls
     * like ->appendStylesheet()->headLink().
     *
     * @param  array  $attributes
     * @param  string $placement
     * @return HeadLink
     */
    public function headLink(array $attributes = null, $placement = Placeholder\Container\AbstractContainer::APPEND)
    {
        return call_user_func_array(array($this, '__invoke'), func_get_args());
    }

    /**
     * headLink() - View Helper Method
     *
     * Returns current object instance. Optionally, allows passing array of
     * values to build link.
     *
     * @param  array  $attributes
     * @param  string $placement
     * @return HeadLink
     */
    public function __invoke(array $attributes = null, $placement = Placeholder\Container\AbstractContainer::APPEND)
    {
        if (null !== $attributes) {
            $item = $this->createData($attributes);
            switch ($placement) {
                case Placeholder\Container\AbstractContainer::SET:
                    $this->set($item);
                    break;
                case Placeholder\Container\AbstractContainer::PREPEND:
                    $this->prepend($item);
                    break;
                case Placeholder\Container\AbstractContainer::APPEND:
                default:
                    $this->append($item);
                    break;
            }
        }

        return $this;
    }

    /**
     * Overload method access
     *
     * Items that may be added in the future:
     * - Navigation?  need to find docs on this
     *   - public function appendStart()
     *   - public function appendContents()
     *   - public function appendPrev()
     *   - public function appendNext()
     *   - public function appendIndex()
     *   - public function appendEnd()
     *   - public function appendGlossary()
     *   - public function appendAppendix()
     *   - public function appendHelp()
     *   - public function appendBookmark()
     * - Other?
     *   - public function appendCopyright()
     *   - public function appendChapter()
     *   - public function appendSection()
     *   - public function appendSubsection()
     *
     * @param  mixed $method
     * @param  mixed $args
     * @throws Exception\BadMethodCallException
     * @return void
     */
    public function __call($method, $args)
    {
        if (preg_match('/^(?P<action>set|(ap|pre)pend|offsetSet)(?P<type>Stylesheet|Alternate|Prev|Next)$/', $method, $matches)) {
            $argc   = count($args);
            $action = $matches['action'];
            $type   = $matches['type'];
            $index  = null;

            if ('offsetSet' == $action) {
                if (0 < $argc) {
                    $index = array_shift($args);
                    --$argc;
                }
            }

            if (1 > $argc) {
                throw new Exception\BadMethodCallException(
                    sprintf('%s requires at least one argument', $method)
                );
            }

            if (is_array($args[0])) {
                $item = $this->createData($args[0]);
            } else {
                $dataMethod = 'createData' . $type;
                $item       = $this->$dataMethod($args);
            }

            if ($item) {
                if ('offsetSet' == $action) {
                    $this->offsetSet($index, $item);
                } else {
                    $this->$action($item);
                }
            }

            return $this;
        }

        return parent::__call($method, $args);
    }

    /**
     * Check if value is valid
     *
     * @param  mixed $value
     * @return bool
     */
    protected function isValid($value)
    {
        if (!$value instanceof stdClass) {
            return false;
        }

        $vars         = get_object_vars($value);
        $keys         = array_keys($vars);
        $intersection = array_intersect($this->itemKeys, $keys);
        if (empty($intersection)) {
            return false;
        }

        return true;
    }

    /**
     * append()
     *
     * @param  array $value
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function append($value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'append() expects a data token; please use one of the custom append*() methods'
            );
        }

        return $this->getContainer()->append($value);
    }

    /**
     * offsetSet()
     *
     * @param  string|int $index
     * @param  array      $value
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function offsetSet($index, $value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'offsetSet() expects a data token; please use one of the custom offsetSet*() methods'
            );
        }

        return $this->getContainer()->offsetSet($index, $value);
    }

    /**
     * prepend()
     *
     * @param  array $value
     * @throws Exception\InvalidArgumentException
     * @return HeadLink
     */
    public function prepend($value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'prepend() expects a data token; please use one of the custom prepend*() methods'
            );
        }

        return $this->getContainer()->prepend($value);
    }

    /**
     * set()
     *
     * @param  array $value
     * @throws Exception\InvalidArgumentException
     * @return HeadLink
     */
    public function set($value)
    {
        if (!$this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'set() expects a data token; please use one of the custom set*() methods'
            );
        }

        return $this->getContainer()->set($value);
    }

    /**
     * Create HTML link element from data item
     *
     * @param  stdClass $item
     * @return string
     */
    public function itemToString(stdClass $item)
    {
        $attributes = (array) $item;
        $link       = '<link';

        foreach ($this->itemKeys as $itemKey) {
            if (isset($attributes[$itemKey])) {
                if (is_array($attributes[$itemKey])) {
                    foreach ($attributes[$itemKey] as $key => $value) {
                        $link .= sprintf(' %s="%s"', $key, ($this->autoEscape) ? $this->escape($value) : $value);
                    }
                } else {
                    $link .= sprintf(
                        ' %s="%s"',
                        $itemKey,
                        ($this->autoEscape) ? $this->escape($attributes[$itemKey]) : $attributes[$itemKey]
                    );
                }
            }
        }

        if (method_exists($this->view, 'plugin')) {
            $link .= ($this->view->plugin('doctype')->isXhtml()) ? ' />' : '>';
        } else {
            $link .= ' />';
        }

        if (($link == '<link />') || ($link == '<link>')) {
            return '';
        }

        if (isset($attributes['conditionalStylesheet'])
            && !empty($attributes['conditionalStylesheet'])
            && is_string($attributes['conditionalStylesheet'])
        ) {
            // inner wrap with comment end and start if !IE
            if (str_replace(' ', '', $attributes['conditionalStylesheet']) === '!IE') {
                $link = '<!-->' . $link . '<!--';
            }
            $link = '<!--[if ' . $attributes['conditionalStylesheet'] . ']>' . $link . '<![endif]-->';
        }

        return $link;
    }

    /**
     * Render link elements as string
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
            $items[] = $this->itemToString($item);
        }

        return $indent . implode($this->escape($this->getSeparator()) . $indent, $items);
    }

    /**
     * Create data item for stack
     *
     * @param  array $attributes
     * @return stdClass
     */
    public function createData(array $attributes)
    {
        return (object) $attributes;
    }

    /**
     * Create item for stylesheet link item
     *
     * @param  array $args
     * @return stdClass|false Returns false if stylesheet is a duplicate
     */
    public function createDataStylesheet(array $args)
    {
        $rel                   = 'stylesheet';
        $type                  = 'text/css';
        $media                 = 'screen';
        $conditionalStylesheet = false;
        $href                  = array_shift($args);

        if ($this->isDuplicateStylesheet($href)) {
            return false;
        }

        if (0 < count($args)) {
            $media = array_shift($args);
            if (is_array($media)) {
                $media = implode(',', $media);
            } else {
                $media = (string) $media;
            }
        }
        if (0 < count($args)) {
            $conditionalStylesheet = array_shift($args);
            if (!empty($conditionalStylesheet) && is_string($conditionalStylesheet)) {
                $conditionalStylesheet = (string) $conditionalStylesheet;
            } else {
                $conditionalStylesheet = null;
            }
        }

        if (0 < count($args) && is_array($args[0])) {
            $extras = array_shift($args);
            $extras = (array) $extras;
        }

        $attributes = compact('rel', 'type', 'href', 'media', 'conditionalStylesheet', 'extras');

        return $this->createData($attributes);
    }

    /**
     * Is the linked stylesheet a duplicate?
     *
     * @param  string $uri
     * @return bool
     */
    protected function isDuplicateStylesheet($uri)
    {
        foreach ($this->getContainer() as $item) {
            if (($item->rel == 'stylesheet') && ($item->href == $uri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create item for alternate link item
     *
     * @param  array $args
     * @throws Exception\InvalidArgumentException
     * @return stdClass
     */
    public function createDataAlternate(array $args)
    {
        if (3 > count($args)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Alternate tags require 3 arguments; %s provided',
                count($args)
            ));
        }

        $rel   = 'alternate';
        $href  = array_shift($args);
        $type  = array_shift($args);
        $title = array_shift($args);

        if (0 < count($args) && is_array($args[0])) {
            $extras = array_shift($args);
            $extras = (array) $extras;

            if (isset($extras['media']) && is_array($extras['media'])) {
                $extras['media'] = implode(',', $extras['media']);
            }
        }

        $href  = (string) $href;
        $type  = (string) $type;
        $title = (string) $title;

        $attributes = compact('rel', 'href', 'type', 'title', 'extras');

        return $this->createData($attributes);
    }

    /**
     * Create item for a prev relationship (mainly used for pagination)
     *
     * @param  array $args
     * @return stdClass
     */
    public function createDataPrev(array $args)
    {
        $rel  = 'prev';
        $href = (string) array_shift($args);

        $attributes = compact('rel', 'href');

        return $this->createData($attributes);
    }

    /**
     * Create item for a prev relationship (mainly used for pagination)
     *
     * @param  array $args
     * @return stdClass
     */
    public function createDataNext(array $args)
    {
        $rel  = 'next';
        $href = (string) array_shift($args);

        $attributes = compact('rel', 'href');

        return $this->createData($attributes);
    }
}
