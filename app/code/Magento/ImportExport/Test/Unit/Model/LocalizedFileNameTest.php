<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\LocalizedFileName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for localized filename model
 */
class LocalizedFileNameTest extends TestCase
{
    /**
     * @var TimezoneInterface|MockObject
     */
    private $timezone;

    /**
     * @var LocalizedFileName
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->timezone = $this->createMock(TimezoneInterface::class);
        $this->model = new LocalizedFileName(
            $this->timezone,
            [
                'Y-m-d_H-i-s',
                'YmdHis'
            ]
        );
    }

    /**
     * @param string $filename
     * @param string $displayName
     * @dataProvider getFileDisplayNameDataProvider
     */
    public function testGetFileDisplayName(string $filename, string $displayName): void
    {
        $this->timezone
            ->method('scopeDate')
            ->willReturnCallback(
                function () {
                    return func_get_args()[1]->modify('+12 hour');
                }
            );
        $this->assertEquals($displayName, $this->model->getFileDisplayName($filename));
    }

    /**
     * @return array
     */
    public function getFileDisplayNameDataProvider(): array
    {
        return [
            [
                'export_products_2021-01-03_13-42-53',
                'export_products_2021-01-04_01-42-53',
            ],
            [
                '20210513102351_export_customers',
                '20210513222351_export_customers',
            ],
            //unknown format
            [
                'export_products_20210103T134253',
                'export_products_20210103T134253',
            ],
        ];
    }
}
