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
namespace Magento\Catalog\Model\Product;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\TestFramework\Helper\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

class ConditionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Condition
     */
    private $model;

    public function testApplyToCollection()
    {
        $collection = $this->getMockedAbstractCollection();
        $this->assertInstanceOf(
            '\Magento\Catalog\Model\Product\Condition',
            $this->model->applyToCollection($collection)
        );
    }

    public function testGetIdsSelect()
    {
        $adapter = $this->getMockedAdapterInterface();
        $this->assertInstanceOf('\Magento\Framework\DB\Select', $this->model->getIdsSelect($adapter));
        $this->model->setTable(null);
        $this->assertEmpty($this->model->getIdsSelect($adapter));
    }

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject('\Magento\Catalog\Model\Product\Condition');
        $this->model->setTable('testTable')
            ->setPkFieldName('testFieldName');
    }

    /**
     * @return AbstractCollection
     */
    private function getMockedAbstractCollection()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Eav\Model\Entity\Collection\AbstractCollection')
            ->setMethods(['joinTable'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('joinTable');

        return $mock;
    }

    /**
     * @return AdapterInterface
     */
    private function getMockedAdapterInterface()
    {
        $mockedDbSelect = $this->getMockedDbSelect();

        $mockBuilder = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->setMethods(['select'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($mockedDbSelect));

        return $mock;
    }

    /**
     * @return Select
     */
    private function getMockedDbSelect()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Framework\DB\Select')
            ->setMethods(['from'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('from')
            ->will($this->returnValue($mock));

        return $mock;
    }
} 