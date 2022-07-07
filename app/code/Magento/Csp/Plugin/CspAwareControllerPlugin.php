<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Api\CspAwareActionInterface;
use Magento\Csp\Model\Collector\ControllerCollector;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RouterInterface;

/**
 * Plugin that registers CSP aware action instance processing current request.
 */
class CspAwareControllerPlugin
{
    /**
     * @var ControllerCollector
     */
    private $collector;

    /**
     * @param ControllerCollector $collector
     */
    public function __construct(ControllerCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * Register matched action instance.
     *
     * @param RouterInterface $router
     * @param ActionInterface|null $matched
     * @return ActionInterface|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMatch(RouterInterface $router, $matched)
    {
        if ($matched && $matched instanceof CspAwareActionInterface) {
            $this->collector->setCurrentActionInstance($matched);
        }

        return $matched;
    }
}
