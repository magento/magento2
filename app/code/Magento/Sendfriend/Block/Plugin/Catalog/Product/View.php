<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sendfriend\Block\Plugin\Catalog\Product;

class View
{
    /**
     * @var \Magento\Sendfriend\Model\Sendfriend
     */
    protected $_sendfriend;

    /**
     * @param \Magento\Sendfriend\Model\Sendfriend $sendfriend
     */
    public function __construct(
        \Magento\Sendfriend\Model\Sendfriend $sendfriend
    ) {
        $this->_sendfriend = $sendfriend;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanEmailToFriend(\Magento\Catalog\Block\Product\View $subject, $result)
    {
        if (!$result) {
            $result = $this->_sendfriend->canEmailToFriend();
        }
        return $result;
    }
}
