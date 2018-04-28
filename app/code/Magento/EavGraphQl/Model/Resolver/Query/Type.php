<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver\Query;

use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Translate type names found by the custom type locator to GraphQL type names.
 *
 * @api
 */
class Type
{
    /**
     * @var CustomAttributeTypeLocatorInterface
     */
    private $typeLocator;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @var array
     */
    private $customTypes;

    /**
     * @param CustomAttributeTypeLocatorInterface $typeLocator
     * @param TypeProcessor $typeProcessor
     * @param $customTypes
     */
    public function __construct(
        CustomAttributeTypeLocatorInterface $typeLocator,
        TypeProcessor $typeProcessor,
        array $customTypes = []
    ) {
        $this->typeLocator = $typeLocator;
        $this->typeProcessor = $typeProcessor;
        $this->customTypes = $customTypes;
    }

    /**
     * Get type name if located, otherwise return blank/configured type if type found is `mixed`
     *
     * @param string $attributeCode
     * @param string $entityType
     * @return string
     * @throws GraphQlInputException
     */
    public function getType(string $attributeCode, string $entityType) : string
    {
        $type = $this->typeLocator->getType($attributeCode, $entityType);

        $isComplexType = strpos($type, '\\') !== false;
        if ($type === TypeProcessor::NORMALIZED_ANY_TYPE) {
            $type = isset($this->customTypes[$attributeCode]) ? $this->customTypes[$attributeCode] : $type;
            return $type;
        } elseif ($type === TypeProcessor::ANY_TYPE) {
            return "";
        } elseif ($isComplexType) {
            try {
                $type = $this->typeProcessor->translateTypeName($type);
            } catch (\InvalidArgumentException $exception) {
                throw new GraphQlInputException(
                    __('Type %1 has no internal representation declared.', [$type]),
                    null,
                    0,
                    false
                );
            }
        } else {
            $type = $type === 'double' ? 'float' : $type;
        }

        return $type;
    }
}
