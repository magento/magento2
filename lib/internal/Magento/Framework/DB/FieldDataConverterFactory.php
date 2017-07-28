<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\ObjectManagerInterface;

/**
 * Create instance of FieldDataConverter with concrete implementation of DataConverterInterface
 * @since 2.2.0
 */
class FieldDataConverterFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.2.0
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
     * @since 2.2.0
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
