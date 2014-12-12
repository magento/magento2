<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
