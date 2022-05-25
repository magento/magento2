<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Parser;

class DataFixtureBeforeTransaction extends DataFixture
{
    /**
     * @param string $attributeClass
     */
    public function __construct(
        string $attributeClass = \Magento\TestFramework\Fixture\DataFixtureBeforeTransaction::class
    ) {
        parent::__construct($attributeClass);
    }
}
