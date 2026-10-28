<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\CspAwareActionInterface;
use Magento\Csp\Api\PolicyCollectorInterface;

/**
 * Asks for route-specific policies from a compatible controller.
 */
class ControllerCollector implements PolicyCollectorInterface
{
    /**
     * @var CspAwareActionInterface|null
     */
    private $controller;

    /**
     * Set the action interface that is responsible for processing current HTTP request.
     *
     * @param CspAwareActionInterface $cspAwareAction
     * @return void
     */
    public function setCurrentActionInstance(CspAwareActionInterface $cspAwareAction): void
    {
        $this->controller = $cspAwareAction;
    }

    /**
     * @inheritDoc
     */
    public function collect(array $defaultPolicies = []): array
    {
        if ($this->controller) {
            return $this->controller->modifyCsp($defaultPolicies);
        }

        return $defaultPolicies;
    }
}
