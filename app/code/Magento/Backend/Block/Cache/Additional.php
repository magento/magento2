<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
