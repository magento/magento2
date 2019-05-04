<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\Component\Column;

use InvalidArgumentException;

/**
 * Class DataTypeConfigProviderPool
 *
 * @package Magento\Catalog\Ui\Component\Column
 * @api
 */
class DataTypeConfigProviderPool
{
    /**
     * @var array
     */
    private $dataTypeConfigProviders;

    /**
     * DataTypeConfigProviderPool constructor.
     *
     * @param DataTypeConfigProviderInterface[] $providers
     */
    public function __construct(
        array $providers = []
    ) {
        $this->dataTypeConfigProviders = $providers;
        $this->validateProviders();
    }

    /**
     * @param string $dataType
     *
     * @return array
     */
    public function getConfig(string $dataType): array
    {
        return isset($this->dataTypeConfigProviders[$dataType]) ?
            $this->dataTypeConfigProviders[$dataType]->getConfig()
            : [];
    }

    /**
     * Check if providers implement required interface
     *
     * @throws \InvalidArgumentException
     */
    private function validateProviders():void
    {
        array_walk(
            $this->dataTypeConfigProviders,
            function ($provider) {
                if (!$provider instanceof DataTypeConfigProviderInterface) {
                    throw new InvalidArgumentException(
                        sprintf(
                            '$provider should implement DataTypeConfigProviderInterface, %s given',
                            get_class($provider)
                        )
                    );
                }
            }
        );
    }
}
