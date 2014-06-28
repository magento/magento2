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
namespace Magento\Catalog\Model\Config\Source;

use Magento\TestFramework\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Config\Source\Category
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\Collection|MockObject
     */
    private $categoryCollection;

    /**
     * @var \Magento\Catalog\Model\Resource\Category|MockObject
     */
    private $category;

    protected function setUp()
    {
        $this->categoryCollection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Category\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->category = $this->getMockBuilder('Magento\Catalog\Model\Resource\Category')
            ->setMethods(['getName', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Model\Resource\Category\CollectionFactory|MockObject $categoryCollectionFactory */
        $categoryCollectionFactory = $this->getMockBuilder('Magento\Catalog\Model\Resource\Category\CollectionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryCollectionFactory->expects($this->any())->method('create')->will(
            $this->returnValue($this->categoryCollection)
        );

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject('Magento\Catalog\Model\Config\Source\Category', [
                'categoryCollectionFactory' => $categoryCollectionFactory
            ]);
    }

    public function testToOptionArray()
    {
        $expect = [
            ['label' => __('-- Please Select a Category --'), 'value' => ''],
            ['label' => 'name', 'value' => 3]
        ];

        $this->categoryCollection->expects($this->once())->method('addAttributeToSelect')->with(
            $this->equalTo('name')
        )->will($this->returnValue($this->categoryCollection));
        $this->categoryCollection->expects($this->once())->method('addRootLevelFilter')->will(
            $this->returnValue($this->categoryCollection)
        );
        $this->categoryCollection->expects($this->once())->method('load');
        $this->categoryCollection->expects($this->any())->method('getIterator')->will(
            $this->returnValue(new \ArrayIterator([$this->category]))
        );

        $this->category->expects($this->once())->method('getName')->will($this->returnValue('name'));
        $this->category->expects($this->once())->method('getId')->will($this->returnValue(3));

        $this->assertEquals($expect, $this->model->toOptionArray());
    }
} 