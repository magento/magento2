<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\RequestGenerator;

class GeneratorCollection
{
    /**
     * @var array|GeneratorInterface[]
     */
    private $generators;

    /**
     * @var General
     */
    private $defaultGenerator;

    /**
     * GeneratorCollection constructor.
     * @param General $defaultGenerator
     * @param GeneratorInterface[] $generators
     */
    public function __construct(GeneratorInterface $defaultGenerator, array $generators)
    {
        $this->defaultGenerator = $defaultGenerator;
        $this->generators = $generators;
    }

    /**
     * @param $type
     * @return GeneratorInterface
     */
    public function getGeneratorForType($type)
    {
        return isset($this->generators[$type]) ? $this->generators[$type] : $this->defaultGenerator;
    }
}
