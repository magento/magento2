<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\ImportExport\Model\Export\AbstractEntity
 */
namespace Magento\ImportExport\Test\Unit\Model\Export;

use Magento\ImportExport\Model\Export\AbstractEntity;
use PHPUnit\Framework\TestCase;

class EntityAbstractTest extends TestCase
{
    /**
     * Test for setter and getter of file name property
     *
     * @covers \Magento\ImportExport\Model\Export\AbstractEntity::getFileName
     * @covers \Magento\ImportExport\Model\Export\AbstractEntity::setFileName
     */
    public function testGetFileNameAndSetFileName()
    {
        /** @var AbstractEntity $model */
        $model = $this->createPartialMock(
            AbstractEntity::class,
            [
                'export',
                'exportItem',
                'getEntityTypeCode',
                'getAttributeCollection',
                '_getHeaderColumns',
                '_getEntityCollection'
            ]
        );

        $testFileName = 'test_file_name';

        $fileName = $model->getFileName();
        $this->assertNull($fileName);

        $model->setFileName($testFileName);
        $this->assertEquals($testFileName, $model->getFileName());

        $fileName = $model->getFileName();
        $this->assertEquals($testFileName, $fileName);
    }
}
