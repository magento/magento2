<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\EavGraphQl\Model\GetAttributesMetadata;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Load EAV attributes by UIDs
 */
class AttributesMetadata implements ResolverInterface
{
    /**
     * @var GetAttributesMetadata
     */
    private GetAttributesMetadata $getAttributesMetadata;

    /**
     * @param GetAttributesMetadata $getAttributesMetadata
     */
    public function __construct(
        GetAttributesMetadata $getAttributesMetadata
    ) {
        $this->getAttributesMetadata = $getAttributesMetadata;
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
        if (empty($args['input']['uids']) || !is_array($args['input']['uids'])) {
            throw new GraphQlInputException(__('Required parameter "%1" of type array.', 'uids'));
        }

        return $this->getAttributesMetadata->execute(
            $args['input']['uids'],
            (int) $context->getExtensionAttributes()->getStore()->getId()
        );
    }
}
