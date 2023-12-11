<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Observer;

use Magento\Csp\Api\CspRendererInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;

/**
 * Adds CSP rendering after HTTP response is generated.
 */
class Render implements ObserverInterface
{
    /**
     * @var CspRendererInterface
     */
    private $cspRenderer;

    /**
     * @param CspRendererInterface $cspRenderer
     */
    public function __construct(CspRendererInterface $cspRenderer)
    {
        $this->cspRenderer = $cspRenderer;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var HttpResponse $response */
        $response = $observer->getEvent()->getData('response');

        $this->cspRenderer->render($response);
    }
}
