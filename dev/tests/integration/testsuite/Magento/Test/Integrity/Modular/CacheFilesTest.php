<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

class CacheFilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $area
     * @dataProvider cacheConfigDataProvider
     */
    public function testCacheConfig($area)
    {
        $validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->expects($this->any())->method('isValidationRequired')->will($this->returnValue(true));

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Cache\Config\Reader $reader */
        $reader = $objectManager->create(
            \Magento\Framework\Cache\Config\Reader::class,
            ['validationState' => $validationStateMock]
        );
        try {
            $reader->read($area);
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function cacheConfigDataProvider()
    {
        return ['global' => ['global'], 'adminhtml' => ['adminhtml'], 'frontend' => ['frontend']];
    }
}
