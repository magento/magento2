<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Model;


class Observer
{
    public function saveVideoAttributes($observer)
    {
        //@todo implement saving video
    }

    public function changeTemplate($observer)
    {
        $observer->getBlock()->setTemplate('Magento_ProductVideo::helper/gallery.phtml');
    }
}