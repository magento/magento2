<?php
/**
 * Integration test for Magento\Backend\Model\Config\Backend\Cookie\Path
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Backend\Cookie;

class PathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Invalid cookie path
     */
    public function testBeforeSaveException()
    {
        $invalidPath = 'invalid path';
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Backend\Model\Config\Backend\Cookie\Lifetime $model */
        $model = $objectManager->create('Magento\Backend\Model\Config\Backend\Cookie\Path');
        $model->setValue($invalidPath);
        $model->save();
    }
}
