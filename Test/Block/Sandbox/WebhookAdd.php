<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Signifyd webhook addition block.
 */
class WebhookAdd extends Form
{
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
    private $webhookAddButton = '[type=submit]';

    /**
     * XPath selector of webhook element added into grid.
     *
     * @var string
     */
    private $webhookAddedElement = '//tr[./td/span/text()="%s" and ./td/span/text()="%s"]';

    /**
     * Map of webhook event select option values on names of events in webhook grid.
     *
     * @var array
     */
    private $webhookEventOptionsMap = [
        'CASE_CREATION' => 'Case Creation',
        'CASE_RESCORE' => 'Case Rescore',
        'CASE_REVIEW' => 'Case Review',
        'GUARANTEE_COMPLETION' => 'Guarantee Completion',
        'CLAIM_REVIEWED' => 'Claim Review'
    ];

    /**
     * Creates new set of webhooks.
     *
     * @param array $signifydData
     * @return void
     */
    public function createWebhooks(array $signifydData)
    {
        foreach ($this->webhookEventOptionsMap as $webhookEventCode => $webhookEventName) {
            $this->setEvent($webhookEventCode);
            $this->setTeam($signifydData['team']);
            $this->setWebhookUrl();

            $this->addWebhook($webhookEventName);
        }
    }

    /**
     * Sets appropriate webhook event select option by code.
     *
     * @param $webhookEventCode
     * @return void
     */
    private function setEvent($webhookEventCode)
    {
        $this->_rootElement->find(sprintf($this->webhookEventOption, $webhookEventCode))
            ->click();
    }

    /**
     * Sets test team select option.
     *
     * @param string $team
     * @return void
     */
    private function setTeam($team)
    {
        $this->_rootElement->find(sprintf($this->webhookTeamOption, $team), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Sets webhook handler url input value.
     *
     * @return void
     */
    private function setWebhookUrl()
    {
        $this->_rootElement->find($this->webhookUrl)->setValue($this->getWebhookUrl());
    }

    /**
     * Add webhook element.
     *
     * Selenium needs to wait until webhook will be added to grid
     * before proceed to next step.
     *
     * @param string $webhookEventName
     * @return void
     */
    private function addWebhook($webhookEventName)
    {
        $this->_rootElement->find($this->webhookAddButton)->click();

        $this->_rootElement->waitUntil(
            function () use ($webhookEventName) {
                return $this->_rootElement->find(
                    sprintf($this->webhookAddedElement, $this->getWebhookUrl(), $webhookEventName),
                    Locator::SELECTOR_XPATH
                )->isVisible() ? true : null;
            }
        );
    }

    /**
     * Gets webhook handler url.
     *
     * @return string
     */
    private function getWebhookUrl()
    {
        return $_ENV['app_frontend_url'] . 'signifyd/webhooks/handler';
    }
}
