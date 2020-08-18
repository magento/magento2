<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Policy;

/**
 * Policies that are used as flags without a value.
 */
class FlagPolicy implements SimplePolicyInterface
{
    public const POLICIES = [
        'upgrade-insecure-requests',
        'block-all-mixed-content'
    ];

    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return '';
    }
}
