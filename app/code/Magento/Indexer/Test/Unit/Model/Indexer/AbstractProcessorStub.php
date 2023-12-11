<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Indexer;

use Magento\Framework\Indexer\AbstractProcessor;

class AbstractProcessorStub extends AbstractProcessor
{
    public const INDEXER_ID = 'stub_indexer_id';
}
