<?php
/**
 * @api
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Backend abstract block
 */
class Template extends Block
{
    /**
     * Magento new loader.
     *
     * @var string
     */
    protected $spinner = '[data-role="spinner"]';

    /**
     * Magento loader.
     *
     * @var string
     */
    protected $loader = '[data-role="loader"]';

    /**
     * Magento varienLoader.js loader.
     *
     * @var string
     */
    protected $loaderOld = '#loading-mask #loading_mask_loader';

    /**
     * Wait until loader will be disappeared.
     *
     * @return void
     */
    public function waitLoader()
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementNotVisible($this->loader);
        $this->waitForElementNotVisible($this->loaderOld);
    }
}
