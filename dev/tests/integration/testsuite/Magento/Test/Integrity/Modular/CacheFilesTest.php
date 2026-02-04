<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
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
        $validationStateMock->expects($this->any())->method('isValidationRequired')->willReturn(true);

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

    public static function cacheConfigDataProvider()
    {
        return ['global' => ['global'], 'adminhtml' => ['adminhtml'], 'frontend' => ['frontend']];
    }
}
