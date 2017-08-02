<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter that aggregates named interpreters and delegates every evaluation to one of them
 * @since 2.0.0
 */
class Composite implements InterpreterInterface
{
    /**
     * Format: array('<name>' => <instance>, ...)
     *
     * @var InterpreterInterface[]
     * @since 2.0.0
     */
    private $interpreters;

    /**
     * Data key that holds name of an interpreter to be used for that data
     *
     * @var string
     * @since 2.0.0
     */
    private $discriminator;

    /**
     * @param InterpreterInterface[] $interpreters
     * @param string $discriminator
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function __construct(array $interpreters, $discriminator)
    {
        foreach ($interpreters as $interpreterName => $interpreterInstance) {
            if (!$interpreterInstance instanceof InterpreterInterface) {
                throw new \InvalidArgumentException(
                    "Interpreter named '{$interpreterName}' is expected to be an argument interpreter instance."
                );
            }
        }
        $this->interpreters = $interpreters;
        $this->discriminator = $discriminator;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function evaluate(array $data)
    {
        if (!isset($data[$this->discriminator])) {
            throw new \InvalidArgumentException(
                sprintf('Value for key "%s" is missing in the argument data.', $this->discriminator)
            );
        }
        $interpreterName = $data[$this->discriminator];
        unset($data[$this->discriminator]);
        $interpreter = $this->getInterpreter($interpreterName);
        return $interpreter->evaluate($data);
    }

    /**
     * Register interpreter instance under a given unique name
     *
     * @param string $name
     * @param InterpreterInterface $instance
     * @return void
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function addInterpreter($name, InterpreterInterface $instance)
    {
        if (isset($this->interpreters[$name])) {
            throw new \InvalidArgumentException("Argument interpreter named '{$name}' has already been defined.");
        }
        $this->interpreters[$name] = $instance;
    }

    /**
     * Retrieve interpreter instance by its unique name
     *
     * @param string $name
     * @return InterpreterInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    protected function getInterpreter($name)
    {
        if (!isset($this->interpreters[$name])) {
            throw new \InvalidArgumentException("Argument interpreter named '{$name}' has not been defined.");
        }
        return $this->interpreters[$name];
    }
}
