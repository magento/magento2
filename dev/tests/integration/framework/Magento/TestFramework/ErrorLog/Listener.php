<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\ErrorLog;

use Magento\TestFramework\Helper;

class Listener implements \PHPUnit\Framework\TestListener
{
    use \PHPUnit\Framework\TestListenerDefaultImplementation;

    /**
     * {@inheritdoc}
     */
    public function startTest(\PHPUnit\Framework\Test $test): void
    {
        $this->logger = Helper\Bootstrap::getObjectManager()->get(\Magento\TestFramework\ErrorLog\Logger::class);
        $this->logger->clearMessages();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(\PHPUnit\Framework\Test $test, $time): void
    {
        if ($test instanceof \PHPUnit\Framework\TestCase) {
            $messages = $this->logger->getMessages();
            try {
                if ($messages) {
                    $test->assertEquals(
                        '',
                        var_export($messages, true),
                        'Errors were added to log during test execution.'
                    );
                }
            } catch (\Exception $e) {
                $test->getTestResultObject()->addError($test, $e, 0);
            }
        }
    }
}
