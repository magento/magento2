<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Api\PolicyRendererInterface;

/**
 * Pool of policy renderers.
 */
class PolicyRendererPool
{
    /**
     * @var PolicyRendererInterface[]
     */
    private $renderers;

    /**
     * @param PolicyRendererInterface[] $renderers
     */
    public function __construct(array $renderers)
    {
        $this->renderers = $renderers;
    }

    /**
     * Get renderer for the given policy.
     *
     * @param PolicyInterface $forPolicy
     * @return PolicyRendererInterface
     * @throws \RuntimeException When it's impossible to find a proper renderer.
     */
    public function getRenderer(PolicyInterface $forPolicy): PolicyRendererInterface
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->canRender($forPolicy)) {
                return $renderer;
            }
        }

        throw new \RuntimeException(sprintf('Failed to find a renderer for policy #%s', $forPolicy->getId()));
    }
}
