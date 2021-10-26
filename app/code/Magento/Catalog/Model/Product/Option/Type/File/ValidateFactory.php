<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ValidateFactory. Creates Validator with type "ExistingValidate"
 */
class ValidateFactory
{
    private ObjectManagerInterface $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Main factory method
     *
     * @return \Zend_Validate
     */
    public function create()
    {
        return $this->objectManager->create(ExistingValidate::class);
    }
}
