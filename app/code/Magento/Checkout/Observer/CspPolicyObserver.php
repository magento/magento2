<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Observer;

use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Translate\InlineInterface;

/**
 * Observer for adding CSP policy for inline translation
 */
class CspPolicyObserver implements ObserverInterface
{
    /**
     * @var InlineInterface
     */
    private InlineInterface $inlineTranslate;

    /**
     * @var DynamicCollector
     */
    private DynamicCollector $dynamicCollector;

    /**
     * @param InlineInterface $inlineTranslate
     * @param DynamicCollector $dynamicCollector
     */
    public function __construct(InlineInterface $inlineTranslate, DynamicCollector $dynamicCollector)
    {
        $this->inlineTranslate = $inlineTranslate;
        $this->dynamicCollector = $dynamicCollector;
    }

    /**
     * Override CSP policy for checkout page wit inline translation
     *
     * @param Observer $observer
     * @return void
     *
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer): void
    {
        if ($this->inlineTranslate->isAllowed()) {
            $policy = new FetchPolicy(
                'script-src',
                false,
                [],
                [],
                true,
                true,
                false,
                [],
                []
            );

            $this->dynamicCollector->add($policy);
        }
    }
}
