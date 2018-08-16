<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

class Version extends Block
{
    /**
     * @var string
     */
    protected $backendVersion = 'magento-version';

    /**
     * Returns dashboard application version
     *
     * @return string
     */
    public function getVersion()
    {
        $currVer = $this->_rootElement->find($this->backendVersion, Locator::SELECTOR_CLASS_NAME)->getText();
        return str_replace('Magento ver.', '', $currVer);
    }
}
