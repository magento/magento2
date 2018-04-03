<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Output;

use Magento\Framework\GraphQl\Config\ConfigElementInterface;
use Magento\Framework\GraphQl\Schema\Type\OutputTypeInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for 'output type' objects compatible with GraphQL schema generator.
 */
class OutputFactory
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
     * Create output type.
     *
     * @param ConfigElementInterface $configElement
     * @return OutputTypeInterface
     */
    public function create(ConfigElementInterface $configElement) : OutputTypeInterface
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
