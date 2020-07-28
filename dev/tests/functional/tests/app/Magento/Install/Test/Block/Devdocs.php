<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Developer Documentation block.
 */
class Devdocs extends Block
{
    /**
     * Developer Documentation title.
     *
     * @var string
     */
    protected $devdocsTitle = '.page-title';

    /**
     * Get Developer Documentation title text.
     *
     * @return string
     */
    public function getDevdocsTitle()
    {
        return $this->_rootElement->find($this->devdocsTitle)->getText();
    }
}
