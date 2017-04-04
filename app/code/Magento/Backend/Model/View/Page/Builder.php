<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Page;

use Magento\Backend\Model\View\Layout;
use Magento\Framework\App;
use Magento\Framework\Event;
use Magento\Framework\View;

class Builder extends View\Page\Builder
{
    /**
     * @return $this
     */
    protected function afterGenerateBlock()
    {
        $this->layout->initMessages();
        return $this;
    }
}
