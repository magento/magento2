<?php
/**
 * Framework for unit tests containing helper methods
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Number of fields is necessary because of the number of fields used by multiple layers
 * of parent classes.
 *
 */
namespace Magento\Framework\TestFramework\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class \Magento\Framework\TestFramework\Unit\BaseTestCase
 *
 * @since 2.0.0
 */
abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * ObjectManager available since setUp()
     *
     * @var ObjectManager
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * Build a basic mock object
     *
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function booleanDataProvider()
    {
        return [[true], [false]];
    }
}
