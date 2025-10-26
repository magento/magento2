<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Abstract class for behavior tests
 */
namespace Magento\ImportExport\Test\Unit\Model\Source\Import;

use Magento\ImportExport\Model\Source\Import\AbstractBehavior;
use PHPUnit\Framework\TestCase;

abstract class AbstractBehaviorTestCase extends TestCase
{
    /**
     * Model for testing
     *
     * @var AbstractBehavior
     */
    protected $_model;

    protected function tearDown(): void
    {
        unset($this->_model);
    }
}
