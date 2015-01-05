<?php
/**
 * Grid row url generator
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Widget\Grid\Row;

class UrlGeneratorId implements \Magento\Backend\Model\Widget\Grid\Row\GeneratorInterface
{
    /**
     * Create url for passed item using passed url model
     *
     * @param \Magento\Framework\Object $item
     * @return string
     */
    public function getUrl($item)
    {
        return $item->getId();
    }
}
