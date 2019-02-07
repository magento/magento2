<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\IsEmailAvailableDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Is Customer Email Available
 */
class IsEmailAvailable implements ResolverInterface
{
    /**
     * @var IsEmailAvailableDataProvider
     */
    private $isEmailAvailableDataProvider;

    /**
     * @param IsEmailAvailableDataProvider $isEmailAvailableDataProvider
     */
    public function __construct(
        IsEmailAvailableDataProvider $isEmailAvailableDataProvider
    ) {
        $this->isEmailAvailableDataProvider = $isEmailAvailableDataProvider;
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
        $email = $args['email'] ?? null;
        if (!$email) {
            throw new GraphQlInputException(__('"Email should be specified'));
        }
        $isEmailAvailable = $this->isEmailAvailableDataProvider->execute($email);

        return [
            'is_email_available' => $isEmailAvailable
        ];
    }
}
