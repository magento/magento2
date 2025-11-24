<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Helper;

use Magento\Framework\DataObject;

/**
 * Test helper for Email DataObject with exception handling
 *
 * This helper extends DataObject to provide test-specific functionality
 * for testing exception scenarios in email sending.
 */
class EmailDataObjectTestHelper extends DataObject
{
    /**
     * @var \Exception|null
     */
    private $exceptionToThrow;

    /**
     * Set exception to be thrown by getStoreId
     *
     * @param \Exception $exception
     * @return void
     */
    public function setException(\Exception $exception)
    {
        $this->exceptionToThrow = $exception;
    }

    /**
     * Get store ID - throws exception if set
     *
     * @return int
     * @throws \Exception
     */
    public function getStoreId()
    {
        if ($this->exceptionToThrow) {
            throw $this->exceptionToThrow;
        }
        return 1;
    }
}
