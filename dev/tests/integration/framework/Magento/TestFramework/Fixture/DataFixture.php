<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Attribute;
use Magento\Store\Model\ScopeInterface;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class DataFixture
{
    /**
     * @param string $type Fixture class name
     * @param array $data Data passed on to the fixture.
     * @param string|null $as Fixture identifier used to retrieve the data returned by the fixture
     * @param string|null $scope Name that refers to scope object in data storage or scope identifier of $scopeType
     * @param int $count Number of instances to generate
     * @param string $scopeType Type of $scope when identifier of scope is used
     */
    public function __construct(
        public string $type,
        public array $data = [],
        public ?string $as = null,
        public ?string $scope = null,
        public int $count = 1,
        public string $scopeType = ScopeInterface::SCOPE_STORE,
    ) {
    }
}
