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

class StoreGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeGroupMock;

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

        $this->subjectMock = $this->getMock('Magento\Store\Model\Resource\Group', array(), array(), '', false);
        $this->storeGroupMock = $this->getMock(
            'Magento\Store\Model\Group',
            array('getId', '__wakeup', 'dataHasChangedFor'),
            array(),
            '',
            false
        );
    }

    /**
     * @param string $matcherMethod
     * @param int|null $storeId
     * @dataProvider storeGroupDataProvider
     */
    public function testBeforeSave($matcherMethod, $storeId)
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeGroupMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $model = new \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\StoreGroup($this->processorMock);
        $model->beforeSave($this->subjectMock, $this->storeGroupMock);
    }

    /**
     * @param string $matcherMethod
     * @param bool $websiteChanged
     * @dataProvider storeGroupWebsiteDataProvider
     */
    public function testChangedWebsiteBeforeSave($matcherMethod, $websiteChanged)
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeGroupMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->storeGroupMock->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            'root_category_id'
        )->will(
            $this->returnValue($websiteChanged)
        );

        $model = new \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\StoreGroup($this->processorMock);
        $model->beforeSave($this->subjectMock, $this->storeGroupMock);
    }

    /**
     * @return array
     */
    public function storeGroupWebsiteDataProvider()
    {
        return array(array('once', true), array('never', false));
    }

    /**
     * @return array
     */
    public function storeGroupDataProvider()
    {
        return array(array('once', null), array('never', 1));
    }
}
