<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

/**
 * Composite dehydrator for resolver result data.
 */
class DehydratorComposite implements DehydratorInterface
{
    /**
     * @var DehydratorInterface[]
     */
    private array $dehydrators = [];

    /**
     * @param DehydratorInterface[] $dehydrators
     */
    public function __construct(array $dehydrators = [])
    {
        $this->dehydrators = $dehydrators;
    }

    /**
     * @inheritdoc
     */
    public function dehydrate(array &$resolvedValue): void
    {
        if (empty($resolvedValue)) {
            return;
        }
        foreach ($this->dehydrators as $dehydrator) {
            $dehydrator->dehydrate($resolvedValue);
        }
    }
}
