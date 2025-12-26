<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Asset;

/**
 * Class SourceFileGeneratorFactory
 *
 * @package Magento\Framework\View\Asset
 */
class SourceFileGeneratorPool
{
    /**
     * Renderer Types
     *
     * @var array
     */
    private $fileGeneratorTypes;

    /**
     * Factory constructor
     *
     * @param SourceFileGeneratorInterface[] $fileGeneratorTypes
     */
    public function __construct(array $fileGeneratorTypes = [])
    {
        $this->fileGeneratorTypes = $fileGeneratorTypes;
    }

    /**
     * Create class instance
     *
     * @param string $generatorType
     *
     * @return SourceFileGeneratorInterface
     */
    public function create($generatorType)
    {
        if (!$this->fileGeneratorTypes[$generatorType]) {
            throw new \LogicException('Wrong file generator type!');
        }

        return $this->fileGeneratorTypes[$generatorType];
    }
}
