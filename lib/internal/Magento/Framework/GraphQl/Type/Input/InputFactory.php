<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Input;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Config\Data\StructureInterface;
use GraphQL\Type\Definition\InputType;

class InputFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $prototypes;

    /**
     * @var array
     */
    private $typeRegistry;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $prototypes
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $prototypes
    ) {
        $this->objectManager = $objectManager;
        $this->prototypes = $prototypes;
    }

    /**
     * @param StructureInterface $structure
     * @return InputType
     */
    public function create(StructureInterface $structure) : InputType
    {
        if (!isset($this->typeRegistry[$structure->getName()])) {
            $this->typeRegistry[$structure->getName()] =
                $this->objectManager->create(
                    $this->prototypes[get_class($structure)],
                    [
                        'structure' => $structure
                    ]
                );
        }
        return $this->typeRegistry[$structure->getName()];
    }
}
