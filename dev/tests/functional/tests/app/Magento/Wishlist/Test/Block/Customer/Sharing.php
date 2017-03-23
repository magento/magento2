<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Block\Customer;

use Magento\Mtf\Block\Form;

/**
 * Class Sharing
 * Sharing wishlist form
 */
class Sharing extends Form
{
    /**
     * Share Wishlist button selector
     *
     * @var string
     */
    protected $shareWishlist = '[type="submit"]';

    /**
     * Click Share Wishlist
     *
     * @return void
     */
    public function shareWishlist()
    {
        $this->_rootElement->find($this->shareWishlist)->click();
    }

    /**
     * Fill Sharing Information form
     *
     * @param array $sharingInfo
     * @return void
     */
    public function fillForm(array $sharingInfo)
    {
        $mapping = $this->dataMapping($sharingInfo);
        $this->_fill($mapping);
    }
}
