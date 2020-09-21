<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\TestFramework\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Framework for unit tests containing helper methods
 *
 * Number of fields is necessary because of the number of fields used by multiple layers
 * of parent classes.
 */
abstract class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * ObjectManager available since setUp()
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * Build a basic mock object
     *
     * @param string $className
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function basicMock($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Boolean data-provider
     *
     * Providing true and false.
     *
     * @return array
     */
    public function booleanDataProvider()
    {
        return [[true], [false]];
    }
}
