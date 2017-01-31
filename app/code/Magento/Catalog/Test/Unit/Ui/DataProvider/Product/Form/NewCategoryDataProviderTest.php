<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form;

use Magento\Catalog\Ui\DataProvider\Product\Form\NewCategoryDataProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class NewCategoryDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NewCategoryDataProvider
     */
    protected $newCategoryDataProvider;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactoryMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->collectionFactoryMock = $this->getMock(CollectionFactory::class, ['create'], [], '', false);
        $this->newCategoryDataProvider = $this->objectManagerHelper->getObject(
            NewCategoryDataProvider::class,
            ['collectionFactory' => $this->collectionFactoryMock]
        );
    }

    public function testGetData()
    {
        $this->assertArrayHasKey('config', $this->newCategoryDataProvider->getData());
    }

    public function testGetMeta()
    {
        $this->assertArrayHasKey('data', $this->newCategoryDataProvider->getMeta());
    }
}
