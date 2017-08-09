<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\ObjectManagerInterface;

/**
 * Create instance of FieldDataConverter with concrete implementation of DataConverterInterface
 */
class FieldDataConverterFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create instance of FieldDataConverter
     *
     * @param string $dataConverterClassName
     * @return FieldDataConverter
     */
    public function create($dataConverterClassName)
    {
        return $this->objectManager->create(
            FieldDataConverter::class,
            [
                'dataConverter' => $this->objectManager->get($dataConverterClassName)
            ]
        );
    }
}
