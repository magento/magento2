<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewsletterGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Newsletter\Model\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * Subscribe newsletter resolver
 */
class SubscribeNewsletter implements ResolverInterface
{
    /**
     * @var Subscriber
     */
    private $subscriber;

    /**
     * Subscriber constructor.
     *
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param Config $newsLetterConfig
     */
    public function __construct(
        SubscriptionManagerInterface $subscriptionManager,
        Config $newsLetterConfig
    ) {
        $this->newsLetterConfig = $newsLetterConfig;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['email']) || !isset($args['email'])) {
            throw new GraphQlInputException(__('"email" value should be specified'));
        } 
        if (empty($args['storeId']) || !isset($args['storeId'])) {
            throw new GraphQlInputException(__('"storeId" value should be specified'));
        }       
        $subscriber = $this->subscriptionManager->subscribe($args['email'], $args['storeId']);

        return [
            'result' => $subscriber->getStatus()
        ];
    }
}
