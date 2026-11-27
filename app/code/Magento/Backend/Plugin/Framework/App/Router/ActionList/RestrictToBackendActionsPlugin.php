<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Plugin\Framework\App\Router\ActionList;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Router\ActionList;

/**
 * Prevents admin area routing to controllers that do not inherit the backend action.
 */
class RestrictToBackendActionsPlugin
{
    /**
     * @var array<string, int>
     */
    private array $allowedActions;

    /**
     * @param string[] $allowedActions
     */
    public function __construct(array $allowedActions = [])
    {
        $this->allowedActions = array_flip(array_map(strtolower(...), array_values($allowedActions)));
    }

    /**
     * Drop the matched admin controller class when it bypasses backend authentication and authorization.
     *
     * @param ActionList $subject
     * @param string|null $result
     * @param string $module
     * @param string|null $area
     * @return string|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(ActionList $subject, ?string $result, $module, $area): ?string
    {
        if ($result === null
            || $area !== FrontNameResolver::AREA_CODE
            || is_subclass_of($result, AbstractAction::class)
            || isset($this->allowedActions[strtolower($result)])
        ) {
            return $result;
        }

        return null;
    }
}
