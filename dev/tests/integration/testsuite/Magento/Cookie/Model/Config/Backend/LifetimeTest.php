<?php
/**
 * Integration test for Magento\Cookie\Model\Config\Backend\Lifetime
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cookie\Model\Config\Backend;

class LifetimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid cookie lifetime: must be numeric
     */
    public function testBeforeSaveException()
    {
        $invalidCookieLifetime = 'invalid lifetime';
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Cookie\Model\Config\Backend\Lifetime $model */
        $model = $objectManager->create('Magento\Cookie\Model\Config\Backend\Lifetime');
        $model->setValue($invalidCookieLifetime);
        $model->save();
    }
}
