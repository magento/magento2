<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Listener;


use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Model\ObjectManager as ObjectManagerMock;

/**
 * The event listener which instantiates ObjectManager before test run
 */
class ReplaceObjectManager extends \PHPUnit_Framework_BaseTestListener
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Replaces ObjectManager before run for each test
     *
     * Replace existing instance of the Application's ObjectManager with an instance,
     * defined in the Unit Test framework
     *
     * This avoids the issue with a not initialized ObjectManager
     * and allows to customize its behaviour with expected for unit testing
     *
     * @param \PHPUnit_Framework_Test $test
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        if (!$this->objectManager) {
            $this->objectManager = new ObjectManagerMock();
        }
        ObjectManager::setInstance($this->objectManager);
    }
}
