<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write\Delete;

use Magento\Framework\EntityManager\Operation\ValidatorPool;

/**
 * Class ValidateDelete
 */
class ValidateDelete
{
    /**
     * @var ValidatorPool
     */
    private $validatorPool;

    /**
     * ValidateDelete constructor.
     *
     * @param ValidatorPool $validatorPool
     */
    public function __construct(
        ValidatorPool $validatorPool
    ) {
        $this->validatorPool = $validatorPool;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     */
    public function execute($entityType, $entity)
    {
        $validators = $this->validatorPool->getValidators($entityType, 'delete');
        foreach ($validators as $validator) {
            $validator->execute($entityType, $entity);
        }
        return $entity;
    }
}
