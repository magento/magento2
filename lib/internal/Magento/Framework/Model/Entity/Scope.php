<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Class Scope
 */
class Scope implements ScopeInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $fallback;

    /**
     * Scope constructor.
     *
     * @param string $identifier
     * @param string $value
     * @param ScopeInterface|null $fallback
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
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return ScopeInterface
     */
    public function getFallback()
    {
        return $this->fallback;
    }
}
