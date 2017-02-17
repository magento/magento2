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
     * @var string
     */
    private $webhookUrl = '#webhookUrl';

    /**
     * @var string
     */
    private $webhookTestTeamOption = './/select[@id="webhookTeams"]//option[text()="test"]';

    /**
     * @var string
     */
    private $webhookEventOption = './/select[@id="webhookEvent"]//option[@value="%s"]';

    /**
     * @var string
     */
    private $webhookAddedElement = '//tr[./td/span/text()="%s" and ./td/span/text()="%s"]';

    /**
     * Add webhook button.
     *
     * @var string
     */
    private $webhookAddButton = '[type=submit]';

    /**
     * List of option values of webhook event select.
     *
     * @var array
     */
    private $webhookEventOptionsList = [
        'CASE_CREATION' => 'Case Creation',
        'CASE_RESCORE' => 'Case Rescore',
        'CASE_REVIEW' => 'Case Review',
        'GUARANTEE_COMPLETION' => 'Guarantee Completion',
        'CLAIM_REVIEWED' => 'Claim Review'
    ];

    public function createWebhooks()
    {
        foreach ($this->webhookEventOptionsList as $webhookEventCode => $webhookEventName) {
            $this->setEvent($webhookEventCode);
            $this->setTeam();
            $this->setWebhookUrl();
            $this->addWebhook($webhookEventName);
        }
    }

    /**
     * @param $webhookEventCode
     * @return void
     */
    private function setEvent($webhookEventCode)
    {
        $this->_rootElement->find(sprintf($this->webhookEventOption, $webhookEventCode), Locator::SELECTOR_XPATH)
            ->click();
    }

    /**
     * @return void
     */
    private function setTeam()
    {
        $this->_rootElement->find($this->webhookTestTeamOption, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * @return void
     */
    private function setWebhookUrl()
    {
        $this->_rootElement->find($this->webhookUrl)->setValue($this->getWebhookUrl());
    }

    /**
     * @param $webhookEventName
     * @return void
     */
    private function addWebhook($webhookEventName)
    {
        $this->_rootElement->find($this->webhookAddButton)->click();

        // We need to wait until webhook will be added to grid
        // before proceed to next step
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
     * @return string
     */
    private function getWebhookUrl()
    {
        return $_ENV['app_frontend_url'] . 'signifyd/webhooks/handler';
    }
}
