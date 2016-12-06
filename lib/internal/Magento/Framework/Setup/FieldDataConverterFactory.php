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
     * FieldDataConverter class name
     */
    const CLASS_NAME = FieldDataConverter::class;

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
     * @param DataConverterInterface $dataConverterClassName
     * @return FieldDataConverter
     */
    public function create(AdapterInterface $connection, DataConverterInterface $dataConverterClassName)
    {
        return $this->objectManager->create(
            self::CLASS_NAME,
            [
                'connection' => $connection,
                'dataConverter' => $this->objectManager->get($dataConverterClassName)
            ]
        );
    }
}
