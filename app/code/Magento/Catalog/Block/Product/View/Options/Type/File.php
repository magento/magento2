<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product options text type block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product\View\Options\Type;

class File extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * Returns info of file
     *
     * @return string
     */
    public function getFileInfo()
    {
        $info = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $this->getOption()->getId());
        if (empty($info)) {
            $info = new \Magento\Framework\DataObject();
        } elseif (is_array($info)) {
            $info = new \Magento\Framework\DataObject($info);
        }
        return $info;
    }
}
