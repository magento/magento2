<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SendFriend\Block\Plugin\Catalog\Product;

/**
 * Class \Magento\SendFriend\Block\Plugin\Catalog\Product\View
 *
 * @since 2.0.0
 */
class View
{
    /**
     * @var \Magento\SendFriend\Model\SendFriend
     * @since 2.0.0
     */
    protected $_sendfriend;

    /**
     * @param \Magento\SendFriend\Model\SendFriend $sendfriend
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function afterCanEmailToFriend(\Magento\Catalog\Block\Product\View $subject, $result)
    {
        if (!$result) {
            $result = $this->_sendfriend->canEmailToFriend();
        }
        return $result;
    }
}
