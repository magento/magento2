<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

/**
 * Class SourceFileGeneratorFactory
 *
 * @package Magento\Framework\View\Asset
 * @since 2.0.0
 */
class SourceFileGeneratorPool
{
    /**
     * Renderer Types
     *
     * @var array
     * @since 2.0.0
     */
    private $fileGeneratorTypes;

    /**
     * Factory constructor
     *
     * @param SourceFileGeneratorInterface[] $fileGeneratorTypes
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create($generatorType)
    {
        if (!$this->fileGeneratorTypes[$generatorType]) {
            throw new \LogicException('Wrong file generator type!');
        }

        return $this->fileGeneratorTypes[$generatorType];
    }
}
