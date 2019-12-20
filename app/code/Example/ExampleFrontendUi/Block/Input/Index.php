<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Example\ExampleFrontendUi\Block\Input;

use Magento\Framework\View\Element\Template;

/**
 * Class Example Input Index.
 */
class Index extends Template
{
    /**
     * Get message input
     *
     * @return string
     */
    public function getMessageData(): string
    {
        return $this->getMessage();
    }
}
