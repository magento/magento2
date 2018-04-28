<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\SignifydConsole;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;

/**
 * Signifyd webhook addition block.
 */
class Webhooks extends Block
{
    /**
     * Map of webhook event select option values on names of events in webhook grid.
     *
     * @var array
     */
    private $webhookEventOptionsMap = [
        'CASE_CREATION' => 'Case Creation',
        'CASE_REVIEW' => 'Case Review',
        'GUARANTEE_COMPLETION' => 'Guarantee Completion'
    ];

    /**
     * XPath selector of webhook element added into grid.
     *
     * @var string
     */
    private $webhookAddedElement = '//table[@id="webhooks"]//tr[./td/span/text()="%s" and ./td/span/text()="%s"]';

    /**
     * Css selector of webhook url input.
     *
     * @var string
     */
    private $webhookUrl = '[id="webhookUrl"]';

    /**
     * XPath selector of test team select option.
     *
     * @var string
     */
    private $webhookTeamOption = './/select[@id="webhookTeams"]//option[text()="%s"]';

    /**
     * Css selector of webhook event select option.
     *
     * @var string
     */
    private $webhookEventOption = 'select[id="webhookEvent"] option[value="%s"]';

    /**
     * Css selector of webhook addition button.
     *
     * @var string
     */
    private $webhookAddButton = '[id="addWebhook"] [type=submit]';

    /**
     * Css selector of delete button in element of webhook grid.
     *
     * @var string
     */
    private $webhookDeleteButton = '[class*="webhook-delete"]';

    /**
     * Css selector of confirming button for deleting webhook.
     *
     * @var string
     */
    private $webhookDeleteConfirmButton = '[class="appriseOuter"] button[value="ok"]';

    /**
     * Creates new set of webhooks, if it not exists.
     *
     * @param string $team
     * @return void
     */
    public function create($team)
    {
        $handlerUrl = $this->getHandlerUrl();

        foreach ($this->webhookEventOptionsMap as $webhookEventCode => $webhookEventName) {
            if ($this->getWebhook($team, $webhookEventName)) {
                continue;
            }

            $this->addWebhook($handlerUrl, $webhookEventCode, $team);
        }
    }

    /**
     * Deletes set of webhooks.
     *
     * @param string $team
     * @return void
     */
    public function cleanup($team)
    {
        foreach ($this->webhookEventOptionsMap as $webhookEventName) {
            if ($webhook = $this->getWebhook($team, $webhookEventName)) {
                $this->deleteWebhook($webhook);
            }
        }
    }

    /**
     * Gets webhook if exists.
     *
     * @param string $team
     * @param string $webhookEventName
     * @return ElementInterface|null
     */
    private function getWebhook($team, $webhookEventName)
    {
        $webhook = $this->_rootElement->find(
            sprintf($this->webhookAddedElement, $team, $webhookEventName),
            Locator::SELECTOR_XPATH
        );

        return $webhook->isPresent() ? $webhook : null;
    }

    /**
     * Delete webhook element with confirmation popup.
     *
     * @param ElementInterface $webhook
     * @return void
     */
    private function deleteWebhook(ElementInterface $webhook)
    {
        $webhook->find($this->webhookDeleteButton)->click();
        $this->_rootElement->find($this->webhookDeleteConfirmButton)->click();
    }

    /**
     * Sets webhook data and add it.
     *
     * @param string $handlerUrl
     * @param string $webhookEventCode
     * @param string $team
     * @return void
     */
    private function addWebhook(
        $handlerUrl,
        $webhookEventCode,
        $team
    ) {
        $this->setEvent($webhookEventCode);
        $this->setTeam($team);
        $this->setUrl($handlerUrl);
        $this->submit();
    }

    /**
     * Sets appropriate webhook event select option by code.
     *
     * @param string $webhookEventCode
     * @return void
     */
    private function setEvent($webhookEventCode)
    {
        $this->_rootElement->find(
            sprintf($this->webhookEventOption, $webhookEventCode)
        )->click();
    }

    /**
     * Sets test team select option.
     *
     * @param string $team
     * @return void
     */
    private function setTeam($team)
    {
        $this->_rootElement->find(
            sprintf($this->webhookTeamOption, $team),
            Locator::SELECTOR_XPATH
        )->click();
    }

    /**
     * Sets webhook handler url input value.
     *
     * @param string $handlerUrl
     * @return void
     */
    private function setUrl($handlerUrl)
    {
        $this->_rootElement->find($this->webhookUrl)->setValue($handlerUrl);
    }

    /**
     * Add webhook element.
     *
     * @return void
     */
    private function submit()
    {
        $this->_rootElement->find($this->webhookAddButton)->click();
    }

    /**
     * Gets webhook handler url.
     *
     * @return string
     */
    private function getHandlerUrl()
    {
        return $_ENV['app_frontend_url'] . 'signifyd/webhooks/handler';
    }
}
