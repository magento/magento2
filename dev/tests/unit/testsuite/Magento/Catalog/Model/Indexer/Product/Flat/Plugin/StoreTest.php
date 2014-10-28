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
namespace Magento\Catalog\Model\Indexer\Product\Flat\Plugin;

class StoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->processorMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Flat\Processor',
            array('markIndexerAsInvalid'),
            array(),
            '',
            false
        );

        $this->subjectMock = $this->getMock('\Magento\Store\Model\Resource\Store', array(), array(), '', false);
        $this->storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            array('getId', '__wakeup', 'dataHasChangedFor'),
            array(),
            '',
            false
        );
    }

    /**
     * @param string $matcherMethod
     * @param int|null $storeId
     * @dataProvider storeDataProvider
     */
    public function testBeforeSave($matcherMethod, $storeId)
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $model = new \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\Store($this->processorMock);
        $model->beforeSave($this->subjectMock, $this->storeMock);
    }

    /**
     * @param string $matcherMethod
     * @param bool $storeGroupChanged
     * @dataProvider storeGroupDataProvider
     */
    public function testBeforeSaveSwitchStoreGroup($matcherMethod, $storeGroupChanged)
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->storeMock->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            'group_id'
        )->will(
            $this->returnValue($storeGroupChanged)
        );

        $model = new \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\Store($this->processorMock);
        $model->beforeSave($this->subjectMock, $this->storeMock);
    }

    /**
     * @return array
     */
    public function storeGroupDataProvider()
    {
        return array(array('once', true), array('never', false));
    }

    /**
     * @return array
     */
    public function storeDataProvider()
    {
        return array(array('once', null), array('never', 1));
    }
}
