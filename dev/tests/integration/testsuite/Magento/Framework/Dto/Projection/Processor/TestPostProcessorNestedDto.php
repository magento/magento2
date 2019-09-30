<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto\Projection\Processor;

use Magento\Framework\Dto\DtoProjection\ProcessorInterface;

/**
 * Test preprocessor
 */
class TestPostProcessorNestedDto implements ProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function execute(array $source): array
    {
        $source['test_dto_array'] = [$source['test_dto1']];
        return $source;
    }
}
