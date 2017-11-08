<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Model\GraphQl;

use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\Model\GraphQl\Clause\ReferenceType;

/**
 * Class ClauseFactory
 */
class ClauseFactory
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
     * Creates a clause
     *
     * @param ReferenceType $referenceType
     * @param string $fieldName
     * @param string $clauseType
     * @param string|array $clauseValue
     * @return Clause
     */
    public function create(
        ReferenceType $referenceType,
        string $fieldName,
        string $clauseType,
        $clauseValue
    ) {
        return $this->objectManager->create(
            Clause::class,
            [
                'referenceType' => $referenceType,
                'fieldName' => $fieldName,
                'clauseType' => $clauseType,
                'clauseValue' => $clauseValue
            ]
        );
    }
}
