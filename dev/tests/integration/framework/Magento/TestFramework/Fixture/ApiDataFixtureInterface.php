<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

/**
 * Interface for api data fixtures
 */
interface ApiDataFixtureInterface
{
    public const SERVICE_CLASS = 'service';
    public const SERVICE_METHOD = 'method';

    /**
     * Get "apply" service class and method names
     *
     * @return array
     */
    public function getService(): array;

    /**
     * Get rollback service class and method names
     *
     * @return array
     */
    public function getRollbackService(): array;

    /**
     * Process service parameters
     *
     * @param array $data
     * @return array
     */
    public function processServiceMethodParameters(array $data): array;

    /**
     * Process rollback service parameters
     *
     * @param array $data
     * @return array
     */
    public function processRollbackServiceMethodParameters(array $data): array;

    /**
     * Process service result
     *
     * @param mixed $data
     * @return array
     */
    public function processServiceResult($data): array;
}
