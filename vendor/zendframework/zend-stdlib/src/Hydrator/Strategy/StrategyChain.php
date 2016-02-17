<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Hydrator\Strategy;

use Traversable;
use Zend\Stdlib\ArrayUtils;

final class StrategyChain implements StrategyInterface
{
    /**
     * Strategy chain for extraction
     *
     * @var StrategyInterface[]
     */
    private $extractionStrategies;

    /**
     * Strategy chain for hydration
     *
     * @var StrategyInterface[]
     */
    private $hydrationStrategies;

    /**
     * Constructor
     *
     * @param array|Traversable $extractionStrategies
     */
    public function __construct($extractionStrategies)
    {
        $extractionStrategies = ArrayUtils::iteratorToArray($extractionStrategies);
        $this->extractionStrategies = array_map(
            function (StrategyInterface $strategy) {
                // this callback is here only to ensure type-safety
                return $strategy;
            },
            $extractionStrategies
        );

        $this->hydrationStrategies = array_reverse($extractionStrategies);
    }

    /**
     * {@inheritDoc}
     */
    public function extract($value)
    {
        foreach ($this->extractionStrategies as $strategy) {
            $value = $strategy->extract($value);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate($value)
    {
        foreach ($this->hydrationStrategies as $strategy) {
            $value = $strategy->hydrate($value);
        }

        return $value;
    }
}
