<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Model\GraphQl;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ConnectiveFactory
 */
class ConnectiveFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }
    /**
     * Create a connective class
     *
     * @param array $conditions
     * @param string|null $operator
     * @return Connective
     */
    public function create(
        array $conditions,
        string $operator = null
    ) {
        return $this->objectManager->create(
            Connective::class,
            [
                'conditions' => $conditions,
                'operator' => new Operator($operator)
            ]
        );
    }
}
