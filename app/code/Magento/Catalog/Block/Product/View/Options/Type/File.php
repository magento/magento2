<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\View\Options\Type;

/**
 * Product options text type block
 *
 * @api
 * @since 2.0.0
 */
class File extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * Returns info of file
     *
     * @return string
     * @since 2.0.0
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
