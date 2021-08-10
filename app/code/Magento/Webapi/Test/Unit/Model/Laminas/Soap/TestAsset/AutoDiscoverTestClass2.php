<?php

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;

/**
 * Test Class
 */
class AutoDiscoverTestClass2
{
    /**
     *
     * @param \Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\AutoDiscoverTestClass1 $test
     * @return bool
     */
    public function add(AutoDiscoverTestClass1 $test)
    {
        return true;
    }

    /**
     * @return \Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\AutoDiscoverTestClass1[]
     */
    public function fetchAll()
    {
        return [
            new AutoDiscoverTestClass1(),
            new AutoDiscoverTestClass1(),
        ];
    }

    /**
     * @param \Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\AutoDiscoverTestClass1[]
     */
    public function addMultiple($test)
    {

    }
}
