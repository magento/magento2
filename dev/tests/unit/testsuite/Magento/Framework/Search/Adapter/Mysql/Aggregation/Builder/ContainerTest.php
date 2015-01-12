<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\TestFramework\Helper\ObjectManager;

class ContainerTest extends \PHPUnit_Framework_TestCase
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
        /** @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container $provider */
        $provider = $this->objectManager->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container',
            ['buckets' => [$bucketName => $bucketValue]]
        );
        $this->assertEquals($bucketValue, $provider->get($bucketName));
    }
}
