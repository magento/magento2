<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter\Compress;

use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * Abstract compression adapter
 */
abstract class AbstractCompressionAlgorithm implements CompressionAlgorithmInterface
{
    /**
     * @var array
     */
    protected $options = array();

    /**
     * Class constructor
     *
     * @param null|array|Traversable $options (Optional) Options to set
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Returns one or all set options
     *
     * @param  string $option (Optional) Option to return
     * @return mixed
     */
    public function getOptions($option = null)
    {
        if ($option === null) {
            return $this->options;
        }

        if (!array_key_exists($option, $this->options)) {
            return;
        }

        return $this->options[$option];
    }

    /**
     * Sets all or one option
     *
     * @param  array $options
     * @return self
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $option) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->$method($option);
            }
        }

        return $this;
    }
}
