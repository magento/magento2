<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Policy;

/**
 * Governs allowed plugin mime-types.
 */
class PluginTypesPolicy implements SimplePolicyInterface
{
    /**
     * @var string[]
     */
    private $types;

    /**
     * @param string[] $types
     */
    public function __construct(array $types)
    {
        if (!$types) {
            throw new \RuntimeException('PluginTypePolicy must be given at least 1 type.');
        }
        $this->types = array_unique($types);
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'plugin-types';
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return implode(' ', $this->getTypes());
    }

    /**
     * Mime types of allowed plugins.
     *
     * Types like "application/x-shockwave-flash", "application/x-java-applet".
     * Will only work if object-src directive != "none".
     *
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
