<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Hydrator\NamingStrategy;

use Zend\Stdlib\Exception\InvalidArgumentException;

class MapNamingStrategy implements NamingStrategyInterface
{
    /**
     * Map for hydrate name conversion.
     *
     * @var array
     */
    protected $mapping = array();

    /**
     * Reversed map for extract name conversion.
     *
     * @var array
     */
    protected $reverse = array();

    /**
     * Initialize.
     *
     * @param array $mapping Map for name conversion on hydration
     * @param array $reverse Reverse map for name conversion on extraction
     */
    public function __construct(array $mapping, array $reverse = null)
    {
        $this->mapping = $mapping;
        $this->reverse = $reverse ?: $this->flipMapping($mapping);
    }

    /**
     * Safelly flip mapping array.
     *
     * @param  array                    $array Array to flip
     * @return array                    Flipped array
     * @throws InvalidArgumentException
     */
    protected function flipMapping(array $array)
    {
        array_walk($array, function ($value) {
            if (!is_string($value) && !is_int($value)) {
                throw new InvalidArgumentException('Mapping array can\'t be flipped because of invalid value');
            }
        });

        return array_flip($array);
    }

    /**
     * Converts the given name so that it can be extracted by the hydrator.
     *
     * @param  string $name The original name
     * @return mixed  The hydrated name
     */
    public function hydrate($name)
    {
        if (array_key_exists($name, $this->mapping)) {
            return $this->mapping[$name];
        }

        return $name;
    }

    /**
     * Converts the given name so that it can be hydrated by the hydrator.
     *
     * @param  string $name The original name
     * @return mixed  The extracted name
     */
    public function extract($name)
    {
        if (array_key_exists($name, $this->reverse)) {
            return $this->reverse[$name];
        }

        return $name;
    }
}
