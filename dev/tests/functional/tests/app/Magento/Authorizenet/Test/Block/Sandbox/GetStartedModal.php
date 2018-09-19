<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Block\Sandbox;

use Magento\Mtf\Block\Block;

/**
 * 'Get started accepting payments' modal window on Authorize.Net sandbox.
 */
class GetStartedModal extends Block
{
    /**
     * 'Got It' button selector.
     * This button is located in notification window which may appear immediately after login.
     *
     * @var string
     */
    private $gotItButton = '#btnGetStartedGotIt';

    /**
     * Accept notification if it appears after login.
     *
     * @return $this
     */
    public function acceptNotification()
    {
        $element = $this->browser->find($this->gotItButton);
        if ($element->isVisible()) {
            $element->click();
        }
        return $this;
    }
}
