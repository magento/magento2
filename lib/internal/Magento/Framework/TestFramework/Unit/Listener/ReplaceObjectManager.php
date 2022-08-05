<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\TestFramework\Unit\Listener;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

/**
 * The event listener which instantiates ObjectManager before test run
 */
class ReplaceObjectManager implements TestListener
{
    use TestListenerDefaultImplementation;
    /**
     * Replaces ObjectManager before run for each test
     *
     * Replace existing instance of the Application's ObjectManager with the mock.
     *
     * This avoids the issue with a not initialized ObjectManager
     * and makes working with ObjectManager predictable as it always contains clear mock for each test
     *
     * @param Test $test
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function startTest(Test $test): void
    {
        if ($test instanceof TestCase) {
            $objectManagerMock = $test->getMockBuilder(ObjectManagerInterface::class)
                ->getMockForAbstractClass();
            $createMockCallback = function ($type) use ($test) {
                return $test->getMockBuilder($type)
                    ->disableOriginalConstructor()
                    ->getMockForAbstractClass();
            };
            $objectManagerMock->method('create')->willReturnCallback($createMockCallback);
            $objectManagerMock->method('get')->willReturnCallback($createMockCallback);
            ObjectManager::setInstance($objectManagerMock);
        }
    }
}
