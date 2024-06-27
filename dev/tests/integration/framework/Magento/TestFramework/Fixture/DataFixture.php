<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class DataFixture
{
    /**
     * @param string $type Fixture class name
     * @param array $data Data passed on to the fixture.
     * @param string|null $as Fixture identifier used to retrieve the data returned by the fixture
     * @param string|null $scope Name of scope data fixture in which the data fixture should be executed
     * @param int $count Number of instances to generate
     */
    public function __construct(
        public string $type,
        public array $data = [],
        public ?string $as = null,
        public ?string $scope = null,
        public int $count = 1
    ) {
    }
}
