<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\FrontendStorageConfigurationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FrontendStorageConfigurationPoolTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Model\FrontendStorageConfigurationPool */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var FrontendStorageConfigurationInterface
     */
    private $defaultStorageConfiguration;

    protected function setUp()
    {
        $this->defaultStorageConfiguration = $this->createMock(FrontendStorageConfigurationInterface::class);
        $productStorageConfiguration = $this->createMock(ProductInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\FrontendStorageConfigurationPool::class,
            [
                'storageConfigurations' => [
                    'default' => $this->defaultStorageConfiguration,
                    'product' => $productStorageConfiguration
                ]
            ]
        );
    }

    public function testGet()
    {
        $this->assertEquals($this->defaultStorageConfiguration, $this->model->get('default'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid pool type with namespace: product
     */
    public function testGetWithException()
    {
        $this->assertEquals($this->defaultStorageConfiguration, $this->model->get('product'));
    }
}
