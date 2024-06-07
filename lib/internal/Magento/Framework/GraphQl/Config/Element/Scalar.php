<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

use Magento\Framework\GraphQl\Config\ConfigElementInterface;

/**
 * Scalar element
 */
class Scalar implements ConfigElementInterface
{
    
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $implementation;

    /**
     * @param string $name
     * @param string $description
     * @param string $implementation
     */
    public function __construct(
        string $name,
        string $description,
        string $implementation
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->implementation = $implementation;
    }

    /**
     * Returns scalar element name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns scalar element description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Returns scalar element implementation
     *
     * @return string
     */
    public function getImplementation(): string
    {
        return $this->implementation;
    }
}
