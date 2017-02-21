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
 * Signifyd webhook grid block.
 */
class WebhookGrid extends Block
{
    /**
     * XPath selector of all elements in webhook grid.
     *
     * @var string
     */
    private $webhooks = '//tr[./td/span/text()="%s"][1]';

    /**
     * Css selector of delete button in element of webhook grid.
     *
     * @var string
     */
    private $webhookDeleteButton = '[class*="webhook-delete"]';

    /**
     * Css selector of popup block for delete webhook confirmation.
     *
     * @var string
     */
    private $webhookDeleteConfirmOverlay = '[class="appriseOuter"]';

    /**
     * Css selector of confirming button for deleting webhook.
     *
     * @var string
     */
    private $webhookDeleteConfirmButton = '[class="appriseOuter"] button[value="ok"]';

    /**
     * Removes all existing webhooks created by Signifyd test team.
     *
     * @param string $team
     * @return void
     */
    public function cleanupByTeam($team)
    {
        while ($webhook = $this->getWebhook($team)) {
            if ($webhook->isPresent()) {
                $this->deleteWebhook($webhook);
                continue;
            }
            break;
        }
    }

    /**
     * Gets all existing webhook elements from grid.
     *
     * @param string $team
     * @return ElementInterface
     */
    private function getWebhook($team)
    {
        return $this->_rootElement->find(sprintf($this->webhooks, $team), Locator::SELECTOR_XPATH);
    }

    /**
     * Delete webhook element with confirmation popup.
     *
     * Signifyd creates the same popup every time Selenium click on delete button,
     * so we need to wait that previous popup will be closed.
     *
     * @param ElementInterface $webhookElement
     * @return void
     */
    private function deleteWebhook(ElementInterface $webhookElement)
    {
        $webhookElement->find($this->webhookDeleteButton)->click();
        $this->_rootElement->find($this->webhookDeleteConfirmButton)->click();

        $this->_rootElement->waitUntil(
            function () {
                return $this->_rootElement->find($this->webhookDeleteConfirmOverlay)
                    ->isVisible() ? null : true;
            }
        );
    }
}
