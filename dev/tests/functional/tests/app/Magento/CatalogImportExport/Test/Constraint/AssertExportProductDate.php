<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\File\ExportInterface;

/**
 * Assert that date fields in exported file are shown in global configuration time zone.
 */
class AssertExportProductDate extends AbstractConstraint
{
    /**
     * Assert that date fields in exported file are shown in global configuration time zone.
     *
     * @param ExportInterface $export
     * @param \DateTime $dateTime
     * @return void
     */
    public function processAssert(ExportInterface $export, \DateTime $dateTime)
    {
        $exportData = $export->getLatest();

        \PHPUnit_Framework_Assert::assertTrue(
            (bool) strpos($exportData->getContent(), $dateTime->format('n/j/y, g')),
            'Date fields in exported file are shown in not global configuration time zone.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Date fields in exported file are shown in global configuration time zone.';
    }
}
