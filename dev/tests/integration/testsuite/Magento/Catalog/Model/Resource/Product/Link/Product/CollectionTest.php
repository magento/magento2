<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Product\Link\Product;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $collection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Resource\Product\Link\Product\Collection'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_crosssell.php
     */
    public function testAddLinkAttributeToFilterWithResults()
    {
        $om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $link = $om->get('\Magento\Catalog\Model\Product\Link')->useCrossSellLinks();
        $this->collection->setLinkModel($link);
        $this->collection->addLinkAttributeToFilter('position', array('from' => 0, 'to' => 2));
        $product = $om->get('Magento\Catalog\Model\Product')->load(2);
        $this->collection->setProduct($product);
        $this->collection->load();
        $this->assertCount(1, $this->collection->getItems());
        foreach ($this->collection as $item) {
            $this->assertGreaterThan(0, $item->getPosition());
            $this->assertLessThan(2, $item->getPosition());
        }

    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_crosssell.php
     */
    public function testAddLinkAttributeToFilterNoResults()
    {
        $om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $link = $om->get('\Magento\Catalog\Model\Product\Link')->useCrossSellLinks();
        $this->collection->setLinkModel($link);
        $this->collection->addLinkAttributeToFilter('position', array('from' => 2, 'to' => 3));
        $product = $om->get('Magento\Catalog\Model\Product')->load(2);
        $this->collection->setProduct($product);
        $this->collection->load();
        $this->assertCount(0, $this->collection->getItems());
    }
}
