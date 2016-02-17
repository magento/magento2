<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Hydrator\NamingStrategy;

final class CompositeNamingStrategy implements NamingStrategyInterface
{
    /**
     * @var array
     */
    private $namingStrategies = array();

    /**
     * @var NamingStrategyInterface
     */
    private $defaultNamingStrategy;

    /**
     * @param NamingStrategyInterface[]    $strategies            indexed by the name they translate
     * @param NamingStrategyInterface|null $defaultNamingStrategy
     */
    public function __construct(array $strategies, NamingStrategyInterface $defaultNamingStrategy = null)
    {
        $this->namingStrategies = array_map(
            function (NamingStrategyInterface $strategy) {
                // this callback is here only to ensure type-safety
                return $strategy;
            },
            $strategies
        );

        $this->defaultNamingStrategy = $defaultNamingStrategy ?: new IdentityNamingStrategy();
    }

    /**
     * {@inheritDoc}
     */
    public function extract($name)
    {
        $strategy = isset($this->namingStrategies[$name])
            ? $this->namingStrategies[$name]
            : $this->defaultNamingStrategy;

        return $strategy->extract($name);
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate($name)
    {
        $strategy = isset($this->namingStrategies[$name])
            ? $this->namingStrategies[$name]
            : $this->defaultNamingStrategy;

        return $strategy->hydrate($name);
    }
}
