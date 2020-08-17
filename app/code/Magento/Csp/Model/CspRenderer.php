<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Csp\Api\CspRendererInterface;
use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;

/**
 * @inheritDoc
 */
class CspRenderer implements CspRendererInterface
{
    /**
     * @var PolicyRendererPool
     */
    private $rendererPool;

    /**
     * @var PolicyCollectorInterface
     */
    private $collector;

    /**
     * @param PolicyRendererPool $rendererPool
     * @param PolicyCollectorInterface $collector
     */
    public function __construct(PolicyRendererPool $rendererPool, PolicyCollectorInterface $collector)
    {
        $this->rendererPool = $rendererPool;
        $this->collector = $collector;
    }

    /**
     * @inheritDoc
     */
    public function render(HttpResponse $response): void
    {
        $policies = $this->collector->collect();
        foreach ($policies as $policy) {
            $this->rendererPool->getRenderer($policy)->render($policy, $response);
        }
    }
}
