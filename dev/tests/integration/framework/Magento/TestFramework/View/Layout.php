<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\View;

class Layout extends \Magento\Framework\View\Layout
{
    /**
     * @var bool
     */
    protected $isCacheable = true;

    /**
     * @return bool
     */
    public function isCacheable()
    {
        return $this->isCacheable && parent::isCacheable();
    }

    /**
     * @param bool $isCacheable
     * @return void
     */
    public function setIsCacheable($isCacheable)
    {
        $this->isCacheable = $isCacheable;
    }
}
