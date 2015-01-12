<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

use Magento\TestFramework\Helper\ObjectManager;

class DataProviderContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
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
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderContainer',
            ['dataProviders' => [$bucketName => $bucketValue]]
        );
        $this->assertEquals($bucketValue, $provider->get($bucketName));
    }
}
