<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\SignifydConsole;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Signifyd login block.
 */
class SignifydLogin extends Form
{
    /**
     * Css selector of Signifyd login button.
     *
     * @var string
     */
    private $loginButton = '[type=submit]';

    /**
     * Locator for admin form notification window.
     *
     * @var string
     */
    private $notificationCloseButton = '.wm-close-button';

    /**
     * @inheritdoc
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $this->closeNotification();

        return parent::fill($fixture, $element);
    }

    /**
     * Login to Signifyd.
     *
     * @return void
     */
    public function login()
    {
        $this->closeNotification();
        $this->_rootElement->find($this->loginButton)->click();
    }

    /**
     * Close notification popup.
     *
     * @return void
     */
    private function closeNotification(): void
    {
        $notification = $this->browser->find($this->notificationCloseButton);
        if ($notification->isVisible()) {
            $notification->click();
        }
    }
}
