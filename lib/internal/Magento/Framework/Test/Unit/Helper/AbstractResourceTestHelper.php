<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Helper;

use Magento\Framework\Model\ResourceModel\AbstractResource;

/**
 * Test helper for AbstractResource
 *
 * This helper extends the concrete AbstractResource class to provide
 * test-specific functionality without dependency injection issues.
 */
class AbstractResourceTestHelper extends AbstractResource
{
    /**
     * @var string
     */
    private $idFieldName = 'id';

    /**
     * Get ID field name
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->idFieldName;
    }

    /**
     * Set ID field name
     *
     * @param string $fieldName
     * @return $this
     */
    public function setIdFieldName($fieldName)
    {
        $this->idFieldName = $fieldName;
        return $this;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        // Empty implementation for testing
    }

    /**
     * Get connection
     *
     * @return mixed
     */
    public function getConnection()
    {
        return null;
    }
}
