<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
            $info = new \Magento\Framework\Object();
        } elseif (is_array($info)) {
            $info = new \Magento\Framework\Object($info);
        }
        return $info;
    }
}
