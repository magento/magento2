<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriendGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SendFriend\Helper\Data as SendFriendHelper;

/**
 * Resolve Store Config information for SendFriend
 */
class SendFriendConfiguration implements ResolverInterface
{
    /**
     * @var SendFriendHelper
     */
    private $sendFriendHelper;

    /**
     * @param SendFriendHelper $sendFriendHelper
     */
    public function __construct(SendFriendHelper $sendFriendHelper)
    {
        $this->sendFriendHelper = $sendFriendHelper;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $store = $context->getExtensionAttributes()->getStore();
        $storeId = $store->getId();

        return [
            'enabled_for_customers' => $this->sendFriendHelper->isEnabled($storeId),
            'enabled_for_guests' => $this->sendFriendHelper->isAllowForGuest($storeId)
        ];
    }
}
