<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block;

use Magento\Mtf\Client\Locator;

/**
 * Catalog messages block.
 */
class Messages extends \Magento\Backend\Test\Block\Messages
{
    /**
     * Selector for "This is a required field." validation error message.
     *
     * @var string
     */
    private $validationErrorMessage = '.mage-error';

    /**
     * Wait for Success message or JS validation error message.
     *
     * @param string $strategy
     * @return bool
     */
    public function waitMessage($strategy = Locator::SELECTOR_CSS)
    {
        $browser = $this->browser;
        $successMessage = $this->successMessage;
        $errorMessage = $this->validationErrorMessage;
        return $browser->waitUntil(
            function () use ($browser, $successMessage, $errorMessage, $strategy) {
                $success = $browser->find($successMessage, $strategy);
                $error = $browser->find($errorMessage, $strategy);
                return $success->isVisible() || $error->isVisible() ? true : null;
            }
        );
    }
}
