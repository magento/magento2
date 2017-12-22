<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;
use Magento\Framework\Locale\ConfigInterface;

/**
 * Define Currency GraphQL type
 */
class Currency implements HandlerInterface
{
    const CURRENCY_TYPE_NAME = 'Currency';

    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var \Magento\Framework\Locale\ConfigInterface
     */
    private $configInterface;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param ConfigInterface $configInterface
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        Pool $typePool,
        ConfigInterface $configInterface,
        TypeFactory $typeFactory
    ) {
        $this->typePool = $typePool;
        $this->configInterface = $configInterface;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->typeFactory->createEnum(
            [
                'name' => self::CURRENCY_TYPE_NAME,
                'values' => array_values($this->configInterface->getAllowedCurrencies()),
            ]
        );
    }
}
