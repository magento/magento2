<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Observer;

use Magento\Framework\App\PageCache\FormKey;

class FlushFormKeyOnLogout
{
    /**
     * @var FormKey
     */
    private $cookieFormKey;

    /**
     * @param FormKey $cookieFormKey
     */
    public function __construct(
        FormKey $cookieFormKey
    ) {
        $this->cookieFormKey = $cookieFormKey;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->cookieFormKey->delete();
    }
}
