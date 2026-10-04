<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

/**
 * Prehydrator interface for resolver data.
 */
interface PrehydratorInterface
{
    /**
     * Pre-hydrates the whole cached record right after cache read.
     *
     * @param array $resolverData
     * @return void
     */
    public function prehydrate(array &$resolverData): void;
}
