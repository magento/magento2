<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Cache\Frontend;

use Magento\Framework\App\Area;
use Magento\Framework\Cache\Backend\Redis;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Area
     */
    private $model;

    /**
     * @ingeritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->factory = $this->objectManager->create(Factory::class);
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

    /**
     * Verify factory will create cache frontend instance with default options in case Redis is not available.
     *
     * @return void
     */
    public function testCreateCacheFrontedInstanceWithFallbackToDefaultOptions(): void
    {
        $options = [
            'backend_options' => [
                'server' => null,
            ],
            'id_prefix' => 'test_prefix',
            'backend' => Redis::class,
        ];

        self::assertInstanceOf(FrontendInterface::class, $this->factory->create($options));
    }
}
