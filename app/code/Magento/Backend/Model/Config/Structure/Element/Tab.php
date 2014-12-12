<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Config\Structure\Element;

class Tab extends AbstractComposite
{
    /**
     * Check whether tab is visible
     *
     * @return bool
     */
    public function isVisible()
    {
        return $this->hasChildren();
    }
}
