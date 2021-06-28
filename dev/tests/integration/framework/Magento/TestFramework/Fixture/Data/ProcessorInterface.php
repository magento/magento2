<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Data;

/**
 * Interface for data fixtures processors
 */
interface ProcessorInterface
{
    /**
     * @param array $data
     * @param $fixture
     * @return array
     */
    public function process(array &$data, $fixture);

    /**
     * @param $fixture
     */
    public function revert($fixture);
}
