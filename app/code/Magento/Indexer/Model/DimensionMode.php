<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

/**
 * DTO to work with dimension mode
 */
class DimensionMode
{
    /**
     * @var array
     */
    private $name;

    /**
     * @var array
     */
    private $modes;

    /**
     * @param string   $name
     * @param string[] $modes
     */
    public function __construct(string $name, array $modes)
    {
        $this->validateModes($modes);
        $this->name = $name;
        $this->modes = $modes;
    }

    /**
     * Returns dimension name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns dimension modes
     *
     * @return string[]
     */
    public function getModes(): array
    {
        return $this->modes;
    }

    /**
     * Validates dimension modes
     *
     * @param array $modes
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateModes(array $modes)
    {
        array_walk($modes, function ($mode) {
            if (!\is_string($mode)) {
                throw new \InvalidArgumentException(
                    (string)new \Magento\Framework\Phrase(
                        sprintf('Dimension mode must be a string')
                    )
                );
            }
        });
    }
}
