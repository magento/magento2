<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Category UID processor class for category uid and category id arguments
 */
class CustomizableOptionUidArgsProcessor implements ArgumentsProcessorInterface
{
    private const ID = 'id';

    private const UID = 'uid';

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param Uid $uidEncoder
     */
    public function __construct(Uid $uidEncoder)
    {
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * Process the customizable options for updateCartItems arguments for uids
     *
     * @param string $fieldName
     * @param array $customizableOptions
     * @return array
     * @throws GraphQlInputException
     */
    public function process(string $fieldName, array $customizableOptions): array
    {
        foreach ($customizableOptions as $key => $option) {
            $idFilter = $option[self::ID] ?? [];
            $uidFilter = $option[self::UID] ?? [];

            if (!empty($idFilter)
                && !empty($uidFilter)
                && $fieldName === 'updateCartItems') {
                throw new GraphQlInputException(
                    __(
                        '`%1` and `%2` can\'t be used for CustomizableOptionInput object at the same time.',
                        [self::ID, self::UID]
                    )
                );
            } elseif (!empty($uidFilter)) {
                $customizableOptions[$key][self::ID] = $this->uidEncoder->decode((string)$uidFilter);
                unset($customizableOptions[$key][self::UID]);
            }
        }
        return $customizableOptions;
    }
}
