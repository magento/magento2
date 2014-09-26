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
namespace Magento\CatalogSearch\Model\Search;

use Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory;

class RequestGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManagerHelper;

    /** @var \Magento\CatalogSearch\Model\Search\RequestGenerator */
    protected $object;

    /** @var  CollectionFactory | \PHPUnit_Framework_MockObject_MockObject */
    protected $productAttributeCollectionFactory;

    public function setUp()
    {
        $this->productAttributeCollectionFactory =
            $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory')
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $this->objectManagerHelper->getObject(
            'Magento\\CatalogSearch\\Model\\Search\\RequestGenerator',
            ['productAttributeCollectionFactory' => $this->productAttributeCollectionFactory]
        );
    }

    /**
     * @return array
     */
    public function attributesProvider()
    {
        return [
            [[0, 0, 0], 'sku', 'static'],
            [[0, 0, 0], 'price', 'static'],
            [[1, 2, 0], 'name', 'text'],
            [[1, 0, 0], 'name2', 'text', false],
            [[1, 2, 1], 'date', 'decimal'],
            [[1, 2, 1], 'attr_int', 'int'],
        ];
    }

    /**
     * @param array $countResult
     * @param string $code
     * @param string $type
     * @param bool $visibleInAdvanced
     * @dataProvider attributesProvider
     */
    public function testGenerate($countResult, $code, $type, $visibleInAdvanced = true)
    {
        $collection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Attribute\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())
            ->method('getIterator')
            ->willReturn(
                new \ArrayIterator(
                    [
                        $this->createAttributeMock($code, $type, $visibleInAdvanced),
                    ]
                )
            );
        $this->productAttributeCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($collection);
        $result = $this->object->generate();

        $this->assertEquals($countResult[0], $this->countVal($result['quick_search_container']['queries']));
        $this->assertEquals($countResult[1], $this->countVal($result['advanced_search_container']['queries']));
        $this->assertEquals($countResult[2], $this->countVal($result['advanced_search_container']['filters']));
    }

    /**
     * Create attribute mock
     *
     * @param string $code
     * @param string $type
     * @param bool $visibleInAdvanced
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createAttributeMock($code, $type, $visibleInAdvanced = true)
    {
        $attribute = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getBackendType', 'getIsVisibleInAdvancedSearch', 'getSearchWeight'])
            ->getMock();
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($code);
        $attribute->expects($this->any())
            ->method('getBackendType')
            ->willReturn($type);

        $attribute->expects($this->any())
            ->method('getSearchWeight')
            ->willReturn(1);

        $attribute->expects($this->any())
            ->method('getIsVisibleInAdvancedSearch')
            ->willReturn($visibleInAdvanced);
        return $attribute;
    }

    private function countVal(&$value)
    {
        return !empty($value) ? count($value) : 0;
    }
}
