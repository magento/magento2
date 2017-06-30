<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Aggregation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataProviderContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testGet()
    {
        $bucketName = 'providerName';
        $bucketValue = 'dataProvider';
        /** @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderContainer $provider */
        $provider = $this->objectManager->getObject(
            \Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderContainer::class,
            ['dataProviders' => [$bucketName => $bucketValue]]
        );
        $this->assertEquals($bucketValue, $provider->get($bucketName));
    }
}
