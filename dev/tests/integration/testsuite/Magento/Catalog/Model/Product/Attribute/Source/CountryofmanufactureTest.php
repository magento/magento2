<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

class CountryofmanufactureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture
     */
    private $model;

    /** @var \Magento\TestFramework\ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture::class
        );
    }

    public function testGetAllOptions()
    {
        $this->cleanAllCache();
        $allOptions = $this->model->getAllOptions();
        $cachedAllOptions = $this->model->getAllOptions();
        $this->assertEquals($allOptions, $cachedAllOptions);
    }

    private function cleanAllCache()
    {
        /** @var \Magento\Framework\App\Cache\Frontend\Pool $cachePool */
        $cachePool = $this->objectManager->get(\Magento\Framework\App\Cache\Frontend\Pool::class);
        /** @var \Magento\Framework\Cache\FrontendInterface $cacheType */
        foreach ($cachePool as $cacheType) {
            $cacheType->getBackend()->clean();
        }
    }
}
