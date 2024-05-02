<?php
/************************************************************************
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\CustomerImportExport\Test\Unit\Plugin\Model\Export;

use Magento\CustomerImportExport\Plugin\Model\Export\FormatCustomerCreatedAt;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\CustomerImportExport\Model\Export\Customer;
use Magento\Customer\Model\Customer as Item;
use PHPUnit\Framework\TestCase;

class FormatCustomerCreatedAtTest extends TestCase
{
    /**
     * @var Customer|MockObject
     */
    private $subjectMock;

    /**
     * @var Item|MockObject
     */
    private $itemMock;

    /**
     * Format customer createdAt plugin model
     *
     * @var FormatCustomerCreatedAt
     */
    private $model;

    protected function setUp(): void
    {
        $timeZone = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->subjectMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new FormatCustomerCreatedAt(
            $timeZone
        );
    }

    public function testBeforeExportIte()
    {
        $this->model->beforeExportItem(
            $this->subjectMock,
            $this->itemMock
        );
    }
}
