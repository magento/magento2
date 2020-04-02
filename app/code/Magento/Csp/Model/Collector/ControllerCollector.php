<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\CspAwareActionInterface;

/**
 * Asks for route-specific policies from a compatible controller.
 */
class ControllerCollector implements PrioritizedPolicyCollectorInterface
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

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return -100;
    }
}
