<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\RequestGenerator;

/**
 * @api
 * @since 2.2.0
 */
class GeneratorResolver
{
    /**
     * @var GeneratorInterface[]
     * @since 2.2.0
     */
    private $generators;

    /**
     * @var GeneratorInterface
     * @since 2.2.0
     */
    private $defaultGenerator;

    /**
     * @param GeneratorInterface $defaultGenerator
     * @param GeneratorInterface[] $generators
     * @since 2.2.0
     */
    public function __construct(GeneratorInterface $defaultGenerator, array $generators)
    {
        $this->defaultGenerator = $defaultGenerator;
        $this->generators = $generators;
    }

    /**
     * @param string $type
     * @return GeneratorInterface
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    public function getGeneratorForType($type)
    {
        $generator = isset($this->generators[$type]) ? $this->generators[$type] : $this->defaultGenerator;
        if (!($generator instanceof GeneratorInterface)) {
            throw new \InvalidArgumentException(
                'Generator must implement ' . GeneratorInterface::class
            );
        }
        return $generator;
    }
}
