<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Soap\Wsdl\ComplexTypeStrategy;

use Zend\Soap\Exception;
use Zend\Soap\Wsdl;
use Zend\Soap\Wsdl\ComplexTypeStrategy\ComplexTypeStrategyInterface as ComplexTypeStrategy;

class Composite implements ComplexTypeStrategy
{
    /**
     * Typemap of Complex Type => Strategy pairs.
     * @var array
     */
    protected $typeMap = array();

    /**
     * Default Strategy of this composite
     * @var string|ComplexTypeStrategy
     */
    protected $defaultStrategy;

    /**
     * Context WSDL file that this composite serves
     * @var Wsdl|null
     */
    protected $context;

    /**
     * Construct Composite WSDL Strategy.
     *
     * @param array $typeMap
     * @param string|ComplexTypeStrategy $defaultStrategy
     */
    public function __construct(
        array $typeMap = array(),
        $defaultStrategy = 'Zend\Soap\Wsdl\ComplexTypeStrategy\DefaultComplexType'
    ) {
        foreach ($typeMap as $type => $strategy) {
            $this->connectTypeToStrategy($type, $strategy);
        }

        $this->defaultStrategy = $defaultStrategy;
    }

    /**
     * Connect a complex type to a given strategy.
     *
     * @param  string $type
     * @param  string|ComplexTypeStrategy $strategy
     * @return Composite
     * @throws Exception\InvalidArgumentException
     */
    public function connectTypeToStrategy($type, $strategy)
    {
        if (!is_string($type)) {
            throw new Exception\InvalidArgumentException('Invalid type given to Composite Type Map.');
        }
        $this->typeMap[$type] = $strategy;
        return $this;
    }

    /**
     * Return default strategy of this composite
     *
     * @return ComplexTypeStrategy
     * @throws Exception\InvalidArgumentException
     */
    public function getDefaultStrategy()
    {
        $strategy = $this->defaultStrategy;
        if (is_string($strategy) && class_exists($strategy)) {
            $strategy = new $strategy;
        }
        if (!($strategy instanceof ComplexTypeStrategy)) {
            throw new Exception\InvalidArgumentException(
                'Default Strategy for Complex Types is not a valid strategy object.'
            );
        }
        $this->defaultStrategy = $strategy;
        return $strategy;
    }

    /**
     * Return specific strategy or the default strategy of this type.
     *
     * @param string $type
     * @return ComplexTypeStrategy
     * @throws Exception\InvalidArgumentException
     */
    public function getStrategyOfType($type)
    {
        if (isset($this->typeMap[$type])) {
            $strategy = $this->typeMap[$type];

            if (is_string($strategy) && class_exists($strategy)) {
                $strategy = new $strategy();
            }

            if (!($strategy instanceof ComplexTypeStrategy)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Strategy for Complex Type "%s" is not a valid strategy object.',
                    $type
                ));
            }
            $this->typeMap[$type] = $strategy;
        } else {
            $strategy = $this->getDefaultStrategy();
        }

        return $strategy;
    }

    /**
     * Method accepts the current WSDL context file.
     *
     * @param  Wsdl $context
     * @return Composite
     */
    public function setContext(Wsdl $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Create a complex type based on a strategy
     *
     * @param  string $type
     * @return string XSD type
     * @throws Exception\InvalidArgumentException
     */
    public function addComplexType($type)
    {
        if (!($this->context instanceof Wsdl)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Cannot add complex type "%s", no context is set for this composite strategy.',
                $type
            ));
        }

        $strategy = $this->getStrategyOfType($type);
        $strategy->setContext($this->context);

        return $strategy->addComplexType($type);
    }
}
