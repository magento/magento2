<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Cache\Frontend;

use Magento\TestFramework\Helper\Bootstrap;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Factory
     */
    private $factory;

    /**
     * @var \Magento\Framework\App\Area
     */
    private $model;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->factory = $this->objectManager->create(
            \Magento\Framework\App\Cache\Frontend\Factory::class
        );
    }

    /**
     * Check RemoteSynchronizedCache
     * Removing any cache item in the RemoteSynchronizedCache must invalidate all cache items
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testRemoteSynchronizedCache()
    {
        $data = 'data';
        $identifier = 'identifier';
        $secondIdentifier = 'secondIdentifier';
        $secondData = 'secondData';

        $frontendOptions = ['backend' => 'remote_synchronized_cache'];
        $this->model = $this->factory->create($frontendOptions);

        //Saving data
        $this->assertTrue($this->model->save($data, $identifier));
        $this->assertTrue($this->model->save($secondData, $secondIdentifier));

        //Checking data
        $this->assertEquals($this->model->load($identifier), $data);
        $this->assertEquals($this->model->load($secondIdentifier), $secondData);

        //Removing data
        sleep(2);
        $this->assertTrue($this->model->remove($secondIdentifier));
        $this->assertTrue($this->model->remove($identifier));
        $this->assertEquals($this->model->load($identifier), false);
        $this->assertEquals($this->model->load($secondIdentifier), false);

        //Saving data
        $this->assertTrue($this->model->save($data, $identifier));
        $this->assertTrue($this->model->save($secondData, $secondIdentifier));

        //Checking data
        $this->assertEquals($this->model->load($identifier), $data);
        $this->assertEquals($this->model->load($secondIdentifier), $secondData);
    }
}
