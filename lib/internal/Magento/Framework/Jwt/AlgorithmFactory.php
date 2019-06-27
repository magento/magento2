<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt;

use Jose\Component\Core\Algorithm;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\AlgorithmManagerFactory;

/**
 * Creates algorithm manager.
 */
class AlgorithmFactory
{
    /**
     * @var AlgorithmManager
     */
    private $manager;

    /**
     * @var AlgorithmManagerFactory
     */
    private $algorithmManagerFactory;

    /**
     * @var Algorithm
     */
    private $algorithm;

    /**
     * @param AlgorithmManagerFactory $algorithmManagerFactory
     * @param Algorithm $algorithm
     */
    public function __construct(AlgorithmManagerFactory $algorithmManagerFactory, Algorithm $algorithm)
    {
        $this->algorithmManagerFactory = $algorithmManagerFactory;
        $this->algorithm = $algorithm;
    }

    /**
     * Returns the name of algorithm.
     *
     * @return string
     */
    public function getAlgorithmName(): string
    {
        return $this->algorithm->name();
    }

    /**
     * Gets algorithm manager.
     *
     * @return AlgorithmManager
     */
    public function getAlgorithmManager(): AlgorithmManager
    {
        if ($this->manager === null) {
            $this->algorithmManagerFactory->add($this->getAlgorithmName(), $this->algorithm);
            $this->manager = $this->algorithmManagerFactory->create([$this->getAlgorithmName()]);
        }

        return $this->manager;
    }
}
