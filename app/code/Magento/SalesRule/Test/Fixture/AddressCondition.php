<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\SalesRule\Model\Rule\Condition\Address;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class AddressCondition implements DataFixtureInterface
{
    public const DEFAULT_DATA = [
        'type' => Address::class,
        'attribute' => null,
        'operator' => '==',
        'value' => null,
        'is_value_processed' => false,
    ];

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        DataObjectFactory  $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as AddressCondition::DEFAULT_DATA.
     */
    public function apply(array $data = []): ?DataObject
    {
        return $this->dataObjectFactory->create(['data' => array_merge(self::DEFAULT_DATA, $data)]);
    }
}
