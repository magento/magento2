<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SendFriend\Block\Plugin\Catalog\Product;

class View
{
    /**
     * @var \Magento\SendFriend\Model\SendFriend
     */
    protected $_sendfriend;

    /**
     * @param \Magento\SendFriend\Model\SendFriend $sendfriend
     */
    public function __construct(
        \Magento\SendFriend\Model\SendFriend $sendfriend
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
