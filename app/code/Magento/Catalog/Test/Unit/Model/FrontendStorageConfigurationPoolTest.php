<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\FrontendStorageConfigurationInterface;
use Magento\Catalog\Model\FrontendStorageConfigurationPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class FrontendStorageConfigurationPoolTest extends TestCase
{
    /** @var FrontendStorageConfigurationPool */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var FrontendStorageConfigurationInterface
     */
    private $defaultStorageConfiguration;

    protected function setUp(): void
    {
        $this->defaultStorageConfiguration =
            $this->getMockForAbstractClass(FrontendStorageConfigurationInterface::class);
        $productStorageConfiguration = $this->getMockForAbstractClass(ProductInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            FrontendStorageConfigurationPool::class,
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

    public function testGetWithException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid pool type with namespace: product');
        $this->assertEquals($this->defaultStorageConfiguration, $this->model->get('product'));
    }
}
