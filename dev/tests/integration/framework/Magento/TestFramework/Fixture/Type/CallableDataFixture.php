<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Type;

use Magento\TestFramework\Fixture\DataFixtureTypeInterface;

/**
 * Callable data fixture type
 */
class CallableDataFixture implements DataFixtureTypeInterface
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(
        callable $callback
    ) {
        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?array
    {
        call_user_func($this->callback);
        return null;
    }

    /**
     * @inheritdoc
     */
    public function revert(array $data = []): void
    {
        $rollbackCallback = null;
        if (is_array($this->callback)) {
            $rollbackCallback = $this->callback;
            $rollbackCallback[1] .= 'Rollback';
        } elseif (is_string($this->callback)) {
            $rollbackCallback = $this->callback;
            $rollbackCallback .= 'Rollback';
        }
        if ($rollbackCallback && is_callable($rollbackCallback)) {
            call_user_func($rollbackCallback);
        }
    }
}
