<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use Traversable;

abstract class AbstractGenerator implements GeneratorInterface
{
    /**
     * Line feed to use in place of EOL
     */
    const LINE_FEED = "\n";

    /**
     * @var bool
     */
    protected $isSourceDirty = true;

    /**
     * @var int|string 4 spaces by default
     */
    protected $indentation = '    ';

    /**
     * @var string
     */
    protected $sourceContent = null;

    /**
     * @param  array $options
     */
    public function __construct($options = array())
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * @param  bool $isSourceDirty
     * @return AbstractGenerator
     */
    public function setSourceDirty($isSourceDirty = true)
    {
        $this->isSourceDirty = (bool) $isSourceDirty;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSourceDirty()
    {
        return $this->isSourceDirty;
    }

    /**
     * @param  string $indentation
     * @return AbstractGenerator
     */
    public function setIndentation($indentation)
    {
        $this->indentation = (string) $indentation;
        return $this;
    }

    /**
     * @return string
     */
    public function getIndentation()
    {
        return $this->indentation;
    }

    /**
     * @param  string $sourceContent
     * @return AbstractGenerator
     */
    public function setSourceContent($sourceContent)
    {
        $this->sourceContent = (string) $sourceContent;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceContent()
    {
        return $this->sourceContent;
    }

    /**
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     * @return AbstractGenerator
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable object; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        foreach ($options as $optionName => $optionValue) {
            $methodName = 'set' . $optionName;
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($optionValue);
            }
        }

        return $this;
    }
}
