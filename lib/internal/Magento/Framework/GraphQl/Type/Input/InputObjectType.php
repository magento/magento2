<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Input;

use Magento\Framework\GraphQl\Config\Data\Type as TypeStructure;

/**
 * Class InputObjectType
 */
class InputObjectType extends \GraphQL\Type\Definition\InputObjectType
{
    public function __construct(
        InputMapper $inputMapper,
        TypeStructure $structure
    ) {
        $config = [
            'name' => $structure->getName(),
            'description' => $structure->getDescription()
        ];
        foreach ($structure->getFields() as $field) {
            if ($field->getType() == $structure->getName()) {
                $type = $this;
            } elseif ($field->isList()) {
                $type = $inputMapper->getFieldRepresentation($field->getItemType());
            } else {
                $type = $inputMapper->getFieldRepresentation($field->getType());
            }
            $config['fields'][$field->getName()] = [
                'name' => $field->getName(),
                'type' => $type
            ];
        }
        parent::__construct($config);
    }
}
