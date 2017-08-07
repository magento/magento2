<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Class Scope
 * @since 2.1.0
 */
class Scope implements ScopeInterface
{
    /**
     * @var string
     * @since 2.1.0
     */
    private $identifier;

    /**
     * @var string
     * @since 2.1.0
     */
    private $value;

    /**
     * @var string
     * @since 2.1.0
     */
    private $fallback;

    /**
     * Scope constructor.
     *
     * @param string $identifier
     * @param string $value
     * @param ScopeInterface|null $fallback
     * @since 2.1.0
     */
    public function __construct(
        $identifier,
        $value,
        ScopeInterface $fallback = null
    ) {
        $this->identifier = $identifier;
        $this->value = $value;
        $this->fallback = $fallback;
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return ScopeInterface
     * @since 2.1.0
     */
    public function getFallback()
    {
        return $this->fallback;
    }
}
