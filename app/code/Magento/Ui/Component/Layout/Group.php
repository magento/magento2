<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\Component\Layout;

use Magento\Ui\Component\AbstractView;

/**
 * Class Group
 */
class Group extends AbstractView
{
    /**
     * @return string
     */
    public function getIsRequired()
    {
        return $this->getData('required') ? 'required' : '';
    }
}
