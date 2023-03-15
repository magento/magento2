<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SendFriend\Block\Plugin\Catalog\Product;

use Magento\Catalog\Block\Product\View as ProductView;
use Magento\SendFriend\Model\SendFriend;

class View
{
    /**
     * @var SendFriend
     */
    protected $_sendfriend;

    /**
     * @param SendFriend $sendfriend
     */
    public function __construct(
        SendFriend $sendfriend
    ) {
        $this->_sendfriend = $sendfriend;
    }

    /**
     * @param ProductView $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanEmailToFriend(ProductView $subject, $result)
    {
        if (!$result) {
            $result = $this->_sendfriend->canEmailToFriend();
        }
        return $result;
    }
}
