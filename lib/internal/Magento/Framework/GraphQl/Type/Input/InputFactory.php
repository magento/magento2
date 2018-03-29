<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Type\Input;

use Magento\Framework\GraphQl\Config\ConfigElementInterface;
use Magento\Framework\GraphQl\Type\Definition\InputType;
use Magento\Framework\ObjectManagerInterface;

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
     * @param ConfigElementInterface $configElement
     * @return InputType
     */
    public function create(ConfigElementInterface $configElement) : InputType
    {
        if (!isset($this->typeRegistry[$configElement->getName()])) {
            $this->typeRegistry[$configElement->getName()] =
                $this->objectManager->create(
                    $this->prototypes[get_class($configElement)],
                    [
                        'configElement' => $configElement
                    ]
                );
        }
        return $this->typeRegistry[$configElement->getName()];
    }
}
