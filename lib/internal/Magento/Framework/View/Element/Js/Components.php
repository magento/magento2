<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Js;

use Magento\Framework\App\State;
use Magento\Framework\View\Element\Template;

class Components extends Template
{
    /**
     * Developer mode
     *
     * @return bool
     */
    public function isDeveloperMode()
    {
        return $this->_appState->getMode() == State::MODE_DEVELOPER;
    }
}
