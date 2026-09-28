<?php
/**
 * Integration test for Magento\Cookie\Model\Config\Backend\Path
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Cookie\Model\Config\Backend;

class PathTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     */
    public function testBeforeSaveException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Invalid cookie path');

        $invalidPath = 'invalid path';
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Cookie\Model\Config\Backend\Lifetime $model */
        $model = $objectManager->create(\Magento\Cookie\Model\Config\Backend\Path::class);
        $model->setValue($invalidPath);
        $model->save();
    }
}
