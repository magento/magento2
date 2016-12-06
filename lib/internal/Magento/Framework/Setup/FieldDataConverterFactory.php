<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\DataConverter\DataConverterInterface;

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
     * @param AdapterInterface $connection
     * @param string $dataConverterClassName
     * @return FieldDataConverter
     */
    public function create(AdapterInterface $connection, $dataConverterClassName)
    {
        return $this->objectManager->create(
            FieldDataConverter::class,
            [
                'connection' => $connection,
                'dataConverter' => $this->objectManager->get($dataConverterClassName)
            ]
        );
    }
}
