<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\EavGraphQl\Model\GetAttributesFormComposite;
use Magento\EavGraphQl\Model\GetAttributesMetadata;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Load EAV attributes associated to a form
 */
class AttributesForm implements ResolverInterface
{
    /**
     * @var GetAttributesFormComposite $getAttributesFormComposite
     */
    private GetAttributesFormComposite $getAttributesFormComposite;

    /**
     * @var GetAttributesMetadata
     */
    private GetAttributesMetadata $getAttributesMetadata;

    /**
     * @param GetAttributesFormComposite $providerFormComposite
     * @param GetAttributesMetadata $getAttributesMetadata
     */
    public function __construct(
        GetAttributesFormComposite $providerFormComposite,
        GetAttributesMetadata $getAttributesMetadata
    ) {
        $this->getAttributesFormComposite = $providerFormComposite;
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

        if (empty($args['formCode'])) {
            throw new GraphQlInputException(__('Required parameter "%1" of type string.', 'formCode'));
        }

        $formCode = $args['formCode'];

        $attributes = $this->getAttributesFormComposite->execute($formCode);
        if ($this->isAnAdminForm($formCode) || $attributes === null) {
            return [
                'items' => [],
                'errors' => [
                    [
                        'type' => 'ENTITY_NOT_FOUND',
                        'message' => (string) __('Form "%form" could not be found.', ['form' => $formCode])
                    ]
                ]
            ];
        }

        return array_merge(
            [
                'formCode' => $formCode
            ],
            $this->getAttributesMetadata->execute(
                $attributes,
                (int)$context->getExtensionAttributes()->getStore()->getId()
            )
        );
    }

    /**
     * Check if passed form formCode is an admin form.
     *
     * @param string $formCode
     * @return bool
     */
    private function isAnAdminForm(string $formCode): bool
    {
        return str_starts_with($formCode, 'adminhtml_');
    }
}
