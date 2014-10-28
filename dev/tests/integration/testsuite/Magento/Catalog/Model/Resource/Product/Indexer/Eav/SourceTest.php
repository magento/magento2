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
namespace Magento\Catalog\Model\Resource\Product\Indexer\Eav;
/**
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class SourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Indexer\Eav\Source
     */
    protected $source;

    /**
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     */
    protected $_eavIndexerProcessor;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->source = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Resource\Product\Indexer\Eav\Source'
        );

        $this->productResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Resource\Product'
        );

        $this->_eavIndexerProcessor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Indexer\Product\Eav\Processor'
        );
    }

    /**
     *  Test reindex for configurable product with both disabled and enabled variations.
     */
    public function testReindexEntitiesForConfigurableProduct()
    {
        /** @var \Magento\Catalog\Model\Resource\Eav\Attribute $attr **/
        $attr = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Eav\Model\Config')
           ->getAttribute('catalog_product', 'test_configurable');
        $attr->setIsFilterable(1)->save();

        $this->_eavIndexerProcessor->reindexAll();

        /** @var \Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection $options **/
        $options = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection'
        );
        $options->setAttributeFilter($attr->getId())->load();
        $optionIds = $options->getAllIds();

        $adapter = $this->productResource->getReadConnection();
        $select = $adapter->select()->from($this->productResource->getTable('catalog_product_index_eav'))
            ->where('entity_id = ?', 1)
            ->where('attribute_id = ?', $attr->getId())
            ->where('value IN (?)', $optionIds);

        $result = $adapter->fetchAll($select);
        $this->assertCount(2, $result);

        /** @var \Magento\Catalog\Model\Product $product1 **/
        $product1 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Catalog\Model\Product');
        $product1 = $product1->load(10);
        $product1->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED)->save();

        /** @var \Magento\Catalog\Model\Product $product2 **/
        $product2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Catalog\Model\Product');
        $product2 = $product2->load(20);
        $product2->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED)->save();

        $result = $adapter->fetchAll($select);
        $this->assertCount(0, $result);
    }
}
