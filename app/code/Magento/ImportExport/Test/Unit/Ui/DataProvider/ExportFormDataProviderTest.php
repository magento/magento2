<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Ui\DataProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Ui\DataProvider\ExportFormDataProvider;
use PHPUnit\Framework\TestCase;

class ExportFormDataProviderTest extends TestCase
{
    /**
     * @var ExportFormDataProvider
     */
    private $dataProvider;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->dataProvider = $objectManager->getObject(ExportFormDataProvider::class);
    }

    public function testGetData()
    {
        $expected = [];

        $this->assertEquals($expected, $this->dataProvider->getData());
    }
}
