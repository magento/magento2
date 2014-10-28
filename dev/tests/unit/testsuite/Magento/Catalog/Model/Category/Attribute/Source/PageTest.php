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
namespace Magento\Catalog\Model\Category\Attribute\Source;

use Magento\Cms\Model\Resource\Block\CollectionFactory;
use Magento\TestFramework\Helper\ObjectManager;

class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $testArray = ['test1', ['test1']];

    /**
     * @var \Magento\Catalog\Model\Category\Attribute\Source\Page
     */
    private $model;

    public function testGetAllOptions()
    {
        $assertArray = $this->testArray;
        array_unshift($assertArray, ['value' => '', 'label' => __('Please select a static block.')]);
        $this->assertEquals($assertArray, $this->model->getAllOptions());
    }

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            '\Magento\Catalog\Model\Category\Attribute\Source\Page',
            [
                'blockCollectionFactory' => $this->getMockedBlockCollectionFactory()
            ]
        );
    }

    /**
     * @return \Magento\Cms\Model\Resource\Block\CollectionFactory
     */
    private function getMockedBlockCollectionFactory()
    {
        $mockedCollection = $this->getMockedCollection();

        $mockBuilder = $this->getMockBuilder('\Magento\Cms\Model\Resource\Block\CollectionFactory');
        $mock = $mockBuilder->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($mockedCollection));

        return $mock;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    private function getMockedCollection()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Framework\Data\Collection');
        $mock = $mockBuilder->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($mock));

        $mock->expects($this->any())
            ->method('toOptionArray')
            ->will($this->returnValue($this->testArray));

        return $mock;
    }
}
 