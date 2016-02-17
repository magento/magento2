<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Router\Http;

use Traversable;
use Zend\I18n\Translator\TranslatorInterface as Translator;
use Zend\Mvc\Router\Exception;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Segment route.
 */
class Segment implements RouteInterface
{
    /**
     * Cache for the encode output.
     *
     * @var array
     */
    protected static $cacheEncode = array();

    /**
     * Map of allowed special chars in path segments.
     *
     * http://tools.ietf.org/html/rfc3986#appendix-A
     * segement      = *pchar
     * pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
     * unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
     * sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
     *               / "*" / "+" / "," / ";" / "="
     *
     * @var array
     */
    protected static $urlencodeCorrectionMap = array(
        '%21' => "!", // sub-delims
        '%24' => "$", // sub-delims
        '%26' => "&", // sub-delims
        '%27' => "'", // sub-delims
        '%28' => "(", // sub-delims
        '%29' => ")", // sub-delims
        '%2A' => "*", // sub-delims
        '%2B' => "+", // sub-delims
        '%2C' => ",", // sub-delims
//      '%2D' => "-", // unreserved - not touched by rawurlencode
//      '%2E' => ".", // unreserved - not touched by rawurlencode
        '%3A' => ":", // pchar
        '%3B' => ";", // sub-delims
        '%3D' => "=", // sub-delims
        '%40' => "@", // pchar
//      '%5F' => "_", // unreserved - not touched by rawurlencode
//      '%7E' => "~", // unreserved - not touched by rawurlencode
    );

    /**
     * Parts of the route.
     *
     * @var array
     */
    protected $parts;

    /**
     * Regex used for matching the route.
     *
     * @var string
     */
    protected $regex;

    /**
     * Map from regex groups to parameter names.
     *
     * @var array
     */
    protected $paramMap = array();

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults;

    /**
     * List of assembled parameters.
     *
     * @var array
     */
    protected $assembledParams = array();

    /**
     * Translation keys used in the regex.
     *
     * @var array
     */
    protected $translationKeys = array();

    /**
     * Create a new regex route.
     *
     * @param  string $route
     * @param  array  $constraints
     * @param  array  $defaults
     */
    public function __construct($route, array $constraints = array(), array $defaults = array())
    {
        $this->defaults = $defaults;
        $this->parts    = $this->parseRouteDefinition($route);
        $this->regex    = $this->buildRegex($this->parts, $constraints);
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @return Segment
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['route'])) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        if (!isset($options['constraints'])) {
            $options['constraints'] = array();
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = array();
        }

        return new static($options['route'], $options['constraints'], $options['defaults']);
    }

    /**
     * Parse a route definition.
     *
     * @param  string $def
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function parseRouteDefinition($def)
    {
        $currentPos = 0;
        $length     = strlen($def);
        $parts      = array();
        $levelParts = array(&$parts);
        $level      = 0;

        while ($currentPos < $length) {
            preg_match('(\G(?P<literal>[^:{\[\]]*)(?P<token>[:{\[\]]|$))', $def, $matches, 0, $currentPos);

            $currentPos += strlen($matches[0]);

            if (!empty($matches['literal'])) {
                $levelParts[$level][] = array('literal', $matches['literal']);
            }

            if ($matches['token'] === ':') {
                if (!preg_match('(\G(?P<name>[^:/{\[\]]+)(?:{(?P<delimiters>[^}]+)})?:?)', $def, $matches, 0, $currentPos)) {
                    throw new Exception\RuntimeException('Found empty parameter name');
                }

                $levelParts[$level][] = array('parameter', $matches['name'], isset($matches['delimiters']) ? $matches['delimiters'] : null);

                $currentPos += strlen($matches[0]);
            } elseif ($matches['token'] === '{') {
                if (!preg_match('(\G(?P<literal>[^}]+)\})', $def, $matches, 0, $currentPos)) {
                    throw new Exception\RuntimeException('Translated literal missing closing bracket');
                }

                $currentPos += strlen($matches[0]);

                $levelParts[$level][] = array('translated-literal', $matches['literal']);
            } elseif ($matches['token'] === '[') {
                $levelParts[$level][] = array('optional', array());
                $levelParts[$level + 1] = &$levelParts[$level][count($levelParts[$level]) - 1][1];

                $level++;
            } elseif ($matches['token'] === ']') {
                unset($levelParts[$level]);
                $level--;

                if ($level < 0) {
                    throw new Exception\RuntimeException('Found closing bracket without matching opening bracket');
                }
            } else {
                break;
            }
        }

        if ($level > 0) {
            throw new Exception\RuntimeException('Found unbalanced brackets');
        }

        return $parts;
    }

    /**
     * Build the matching regex from parsed parts.
     *
     * @param  array   $parts
     * @param  array   $constraints
     * @param  int $groupIndex
     * @return string
     */
    protected function buildRegex(array $parts, array $constraints, &$groupIndex = 1)
    {
        $regex = '';

        foreach ($parts as $part) {
            switch ($part[0]) {
                case 'literal':
                    $regex .= preg_quote($part[1]);
                    break;

                case 'parameter':
                    $groupName = '?P<param' . $groupIndex . '>';

                    if (isset($constraints[$part[1]])) {
                        $regex .= '(' . $groupName . $constraints[$part[1]] . ')';
                    } elseif ($part[2] === null) {
                        $regex .= '(' . $groupName . '[^/]+)';
                    } else {
                        $regex .= '(' . $groupName . '[^' . $part[2] . ']+)';
                    }

                    $this->paramMap['param' . $groupIndex++] = $part[1];
                    break;

                case 'optional':
                    $regex .= '(?:' . $this->buildRegex($part[1], $constraints, $groupIndex) . ')?';
                    break;

                case 'translated-literal':
                    $regex .= '#' . $part[1] . '#';
                    $this->translationKeys[] = $part[1];
                    break;
            }
        }

        return $regex;
    }

    /**
     * Build a path.
     *
     * @param  array   $parts
     * @param  array   $mergedParams
     * @param  bool    $isOptional
     * @param  bool    $hasChild
     * @param  array   $options
     * @return string
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    protected function buildPath(array $parts, array $mergedParams, $isOptional, $hasChild, array $options)
    {
        if ($this->translationKeys) {
            if (!isset($options['translator']) || !$options['translator'] instanceof Translator) {
                throw new Exception\RuntimeException('No translator provided');
            }

            $translator = $options['translator'];
            $textDomain = (isset($options['text_domain']) ? $options['text_domain'] : 'default');
            $locale     = (isset($options['locale']) ? $options['locale'] : null);
        }

        $path      = '';
        $skip      = true;
        $skippable = false;

        foreach ($parts as $part) {
            switch ($part[0]) {
                case 'literal':
                    $path .= $part[1];
                    break;

                case 'parameter':
                    $skippable = true;

                    if (!isset($mergedParams[$part[1]])) {
                        if (!$isOptional || $hasChild) {
                            throw new Exception\InvalidArgumentException(sprintf('Missing parameter "%s"', $part[1]));
                        }

                        return '';
                    } elseif (!$isOptional || $hasChild || !isset($this->defaults[$part[1]]) || $this->defaults[$part[1]] !== $mergedParams[$part[1]]) {
                        $skip = false;
                    }

                    $path .= $this->encode($mergedParams[$part[1]]);

                    $this->assembledParams[] = $part[1];
                    break;

                case 'optional':
                    $skippable    = true;
                    $optionalPart = $this->buildPath($part[1], $mergedParams, true, $hasChild, $options);

                    if ($optionalPart !== '') {
                        $path .= $optionalPart;
                        $skip  = false;
                    }
                    break;

                case 'translated-literal':
                    $path .= $translator->translate($part[1], $textDomain, $locale);
                    break;
            }
        }

        if ($isOptional && $skippable && $skip) {
            return '';
        }

        return $path;
    }

    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::match()
     * @param  Request     $request
     * @param  string|null $pathOffset
     * @param  array       $options
     * @return RouteMatch|null
     * @throws Exception\RuntimeException
     */
    public function match(Request $request, $pathOffset = null, array $options = array())
    {
        if (!method_exists($request, 'getUri')) {
            return;
        }

        $uri  = $request->getUri();
        $path = $uri->getPath();

        $regex = $this->regex;

        if ($this->translationKeys) {
            if (!isset($options['translator']) || !$options['translator'] instanceof Translator) {
                throw new Exception\RuntimeException('No translator provided');
            }

            $translator = $options['translator'];
            $textDomain = (isset($options['text_domain']) ? $options['text_domain'] : 'default');
            $locale     = (isset($options['locale']) ? $options['locale'] : null);

            foreach ($this->translationKeys as $key) {
                $regex = str_replace('#' . $key . '#', $translator->translate($key, $textDomain, $locale), $regex);
            }
        }

        if ($pathOffset !== null) {
            $result = preg_match('(\G' . $regex . ')', $path, $matches, null, $pathOffset);
        } else {
            $result = preg_match('(^' . $regex . '$)', $path, $matches);
        }

        if (!$result) {
            return;
        }

        $matchedLength = strlen($matches[0]);
        $params        = array();

        foreach ($this->paramMap as $index => $name) {
            if (isset($matches[$index]) && $matches[$index] !== '') {
                $params[$name] = $this->decode($matches[$index]);
            }
        }

        return new RouteMatch(array_merge($this->defaults, $params), $matchedLength);
    }

    /**
     * assemble(): Defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = array(), array $options = array())
    {
        $this->assembledParams = array();

        return $this->buildPath(
            $this->parts,
            array_merge($this->defaults, $params),
            false,
            (isset($options['has_child']) ? $options['has_child'] : false),
            $options
        );
    }

    /**
     * getAssembledParams(): defined by RouteInterface interface.
     *
     * @see    RouteInterface::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return $this->assembledParams;
    }

    /**
     * Encode a path segment.
     *
     * @param  string $value
     * @return string
     */
    protected function encode($value)
    {
        $key = (string) $value;
        if (!isset(static::$cacheEncode[$key])) {
            static::$cacheEncode[$key] = rawurlencode($value);
            static::$cacheEncode[$key] = strtr(static::$cacheEncode[$key], static::$urlencodeCorrectionMap);
        }
        return static::$cacheEncode[$key];
    }

    /**
     * Decode a path segment.
     *
     * @param  string $value
     * @return string
     */
    protected function decode($value)
    {
        return rawurldecode($value);
    }
}
