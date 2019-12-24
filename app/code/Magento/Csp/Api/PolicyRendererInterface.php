<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Api;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;

/**
 * Renders one policy at a time.
 *
 * Different type of CSPs may require specific renderers due to being represented by different headers.
 */
interface PolicyRendererInterface
{
    /**
     * Render a policy for a response.
     *
     * @param PolicyInterface $policy
     * @param HttpResponse $response
     * @return void
     */
    public function render(PolicyInterface $policy, HttpResponse $response): void;

    /**
     * Would this renderer work for given policy?
     *
     * @param PolicyInterface $policy
     * @return bool
     */
    public function canRender(PolicyInterface $policy): bool;
}
