<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

class CacheFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $area
     * @dataProvider cacheConfigDataProvider
     */
    public function testCacheConfig($area)
    {
        $validationStateMock = $this->getMock('Magento\Framework\Config\ValidationStateInterface');
        $validationStateMock->expects($this->any())->method('isValidated')->will($this->returnValue(true));

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Cache\Config\Reader $reader */
        $reader = $objectManager->create(
            'Magento\Framework\Cache\Config\Reader',
            ['validationState' => $validationStateMock]
        );
        try {
            $reader->read($area);
        } catch (\Magento\Framework\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function cacheConfigDataProvider()
    {
        return ['global' => ['global'], 'adminhtml' => ['adminhtml'], 'frontend' => ['frontend']];
    }
}
