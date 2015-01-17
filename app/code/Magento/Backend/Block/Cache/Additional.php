<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Cache;

class Additional extends \Magento\Backend\Block\Template
{
    /**
     * @return string
     */
    public function getCleanImagesUrl()
    {
        return $this->getUrl('*/*/cleanImages');
    }

    /**
     * @return string
     */
    public function getCleanMediaUrl()
    {
        return $this->getUrl('*/*/cleanMedia');
    }
}
