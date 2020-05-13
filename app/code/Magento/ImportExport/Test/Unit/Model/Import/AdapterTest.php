<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Import;

use Magento\ImportExport\Model\Import\Adapter as Adapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdapterTest extends TestCase
{
    /**
     * @var Adapter|MockObject
     */
    protected $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(\Magento\ImportExport\Model\Import\Adapter::class);
    }

    public function testFactory()
    {
        $this->markTestSkipped('Skipped because factory method has static modifier');
    }

    public function testFindAdapterFor()
    {
        $this->markTestSkipped('Skipped because findAdapterFor method has static modifier');
    }
}
