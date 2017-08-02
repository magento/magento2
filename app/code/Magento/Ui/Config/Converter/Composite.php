<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Converter;

use Magento\Ui\Config\ConverterInterface;

/**
 * Composite converter
 *
 * Identify required converter
 * @since 2.2.0
 */
class Composite implements ConverterInterface
{
    /**
     * Format: array('<name>' => <instance>, ...)
     *
     * @var ConverterInterface[]
     * @since 2.2.0
     */
    private $converters;

    /**
     * Data key that holds name of an converter to be used for that data
     *
     * @var string
     * @since 2.2.0
     */
    private $discriminator;

    /**
     * @param ConverterInterface[] $converters
     * @param string $discriminator
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    public function __construct(array $converters, $discriminator)
    {
        foreach ($converters as $converterName => $converterInstance) {
            if (!$converterInstance instanceof ConverterInterface) {
                throw new \InvalidArgumentException(
                    "Converter named '{$converterName}' is expected to be an argument converter instance."
                );
            }
        }
        $this->converters = $converters;
        $this->discriminator = $discriminator;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    public function convert(\DOMNode $node, array $data)
    {
        if (!isset($data[$this->discriminator])) {
            throw new \InvalidArgumentException(
                sprintf('Value for key "%s" is missing in the argument data.', $this->discriminator)
            );
        }
        $converterName = $data[$this->discriminator];
        unset($data[$this->discriminator]);
        $converter = $this->getConverter($converterName);
        return $converter->convert($node, $data);
    }

    /**
     * Register parser instance under a given unique name
     *
     * @param string $name
     * @param ConverterInterface $instance
     * @return void
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    public function addConverter($name, ConverterInterface $instance)
    {
        if (isset($this->converters[$name])) {
            throw new \InvalidArgumentException("Argument converter named '{$name}' has already been defined.");
        }
        $this->converters[$name] = $instance;
    }

    /**
     * Retrieve parser instance by its unique name
     *
     * @param string $name
     * @return ConverterInterface
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    private function getConverter($name)
    {
        if (!isset($this->converters[$name])) {
            throw new \InvalidArgumentException("Argument converter named '{$name}' has not been defined.");
        }
        return $this->converters[$name];
    }
}
