<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Constraint;

use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check error message list after check data fail.
 */
class AssertImportCheckDataErrorMessagesList extends AbstractConstraint
{
    /**
     * Assert that error message is present.
     *
     * @param array $patterns
     * @param AdminImportIndex $adminImportIndex
     * @return void
     */
    public function processAssert(array $patterns, AdminImportIndex $adminImportIndex)
    {
        $messages = $adminImportIndex->getMessagesBlock()->getErrorsList();

        \PHPUnit\Framework\Assert::assertNotFalse($messages, 'Errors messages block is absent.');
        \PHPUnit\Framework\Assert::assertNotEmpty($messages, 'Errors messages is absent.');

        $errors = [];
        foreach ($messages as $message) {
            if ($this->isNotMatched($patterns, $message)) {
                $errors[] = sprintf('This message "%s" mismatch with any pattern', $message);
            }
        }

        \PHPUnit\Framework\Assert::assertEmpty(
            $errors,
            'This assertions contains next errors:' . PHP_EOL . implode(PHP_EOL, $errors)
        );
    }

    /**
     * Checking message.
     *
     * @param array $patterns
     * @param string $message
     * @return bool
     */
    private function isNotMatched(array $patterns, $message)
    {
        $isNotMatch = true;
        foreach ($patterns as $parts) {
            $parts = (array) $parts;
            if ($isNotMatch && $this->match($message, $parts) === count($parts)) {
                $isNotMatch = false;
            }
        }

        return $isNotMatch;
    }

    /**
     * Check if patterns are contained in a message.
     *
     * @param string $message
     * @param array $patterns
     * @return int
     */
    private function match($message, array $patterns)
    {
        $matchCount = 0;
        foreach ($patterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                ++$matchCount;
            }
        }

        return $matchCount;
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'All messages for errors match the patterns.';
    }
}
