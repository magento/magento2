<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Sandbox;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;

/**
 * Signifyd webhooks list block.
 */
class WebhookList extends Block
{
    /**
     * Name of Signifyd team for testing
     *
     * @var string
     */
    private $team = 'test';

    /**
     * @var string
     */
    private $webhooks = '//tr[contains(@webhooks, "webhooks")]';

    /**
     * @var string
     */
    private $webhookTeamDisplay = '.webhook-team-display';

    /**
     * @var string
     */
    private $webhookDeleteButton = '.webhook-delete';

    /**
     * @var string
     */
    private $webhookDeleteOverlay = '.appriseOuter';

    /**
     * @var string
     */
    private $webhookDeleteConfirm = './/button[contains(@value, "ok")]';

    /**
     * @return void
     */
    public function cleanup()
    {
        foreach ($this->getWebhooks() as $webhook) {
            if ($webhook->isPresent() && $this->getWebhookTeam($webhook) === $this->team) {
                $this->deleteWebhook($webhook);
            }
        }
    }

    /**
     * @return ElementInterface[]
     */
    private function getWebhooks()
    {
        return $this->_rootElement->getElements($this->webhooks, Locator::SELECTOR_XPATH);
    }

    /**
     * @param ElementInterface $webhookElement
     * @return array|string
     */
    private function getWebhookTeam(ElementInterface $webhookElement)
    {
        return $webhookElement->find($this->webhookTeamDisplay)->getText();
    }

    /**
     * @param ElementInterface $webhookElement
     * @return void
     */
    private function deleteWebhook(ElementInterface $webhookElement)
    {
        $webhookElement->find($this->webhookDeleteButton)->click();
        $this->_rootElement->find($this->webhookDeleteConfirm, Locator::SELECTOR_XPATH)->click();

        // Signifyd create the same popup every time Selenium click on delete button
        // So we need to wait that previous popup will be closed
        $this->_rootElement->waitUntil(
            function () {
                return $this->_rootElement->find($this->webhookDeleteOverlay)
                    ->isVisible() ? null : true;
            }
        );
    }
}
