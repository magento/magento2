<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\View\Layout;

use Magento\Framework\App;
use Magento\Framework\Event;
use Magento\Framework\View;

/**
 * @api
 * @since 100.0.2
 */
class Builder extends \Magento\Framework\View\Layout\Builder
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
