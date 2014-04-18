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
namespace Magento\Catalog\Model\Indexer\Category\Flat\System\Config;

class ModeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\System\Config\Mode
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerStateMock;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flatIndexerMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->indexerStateMock = $this->getMock(
            'Magento\Indexer\Model\Indexer\State',
            array('loadByIndexer', 'setStatus', 'save', '__wakeup'),
            array(),
            '',
            false
        );
        $this->flatIndexerMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\IndexerInterface',
            array(),
            '',
            false,
            false,
            true,
            array('load', 'setScheduled', '__wakeup')
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\Indexer\Category\Flat\System\Config\Mode',
            array(
                'config' => $this->configMock,
                'indexerState' => $this->indexerStateMock,
                'flatIndexer' => $this->flatIndexerMock
            )
        );
    }

    public function dataProviderProcessValueEqual()
    {
        return array(array('0', '0'), array('', '0'), array('0', ''), array('1', '1'));
    }

    /**
     * @param string $oldValue
     * @param string $value
     * @dataProvider dataProviderProcessValueEqual
     */
    public function testProcessValueEqual($oldValue, $value)
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            null,
            'default'
        )->will(
            $this->returnValue($oldValue)
        );

        $this->model->setValue($value);

        $this->indexerStateMock->expects($this->never())->method('loadByIndexer');
        $this->indexerStateMock->expects($this->never())->method('setStatus');
        $this->indexerStateMock->expects($this->never())->method('save');

        $this->flatIndexerMock->expects($this->never())->method('load');
        $this->flatIndexerMock->expects($this->never())->method('setScheduled');

        $this->model->processValue();
    }

    public function dataProviderProcessValueOn()
    {
        return array(array('0', '1'), array('', '1'));
    }

    /**
     * @param string $oldValue
     * @param string $value
     * @dataProvider dataProviderProcessValueOn
     */
    public function testProcessValueOn($oldValue, $value)
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            null,
            'default'
        )->will(
            $this->returnValue($oldValue)
        );

        $this->model->setValue($value);

        $this->indexerStateMock->expects(
            $this->once()
        )->method(
            'loadByIndexer'
        )->with(
            'catalog_category_flat'
        )->will(
            $this->returnSelf()
        );
        $this->indexerStateMock->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            'invalid'
        )->will(
            $this->returnSelf()
        );
        $this->indexerStateMock->expects($this->once())->method('save')->will($this->returnSelf());

        $this->flatIndexerMock->expects($this->never())->method('load');
        $this->flatIndexerMock->expects($this->never())->method('setScheduled');

        $this->model->processValue();
    }

    public function dataProviderProcessValueOff()
    {
        return array(array('1', '0'), array('1', ''));
    }

    /**
     * @param string $oldValue
     * @param string $value
     * @dataProvider dataProviderProcessValueOff
     */
    public function testProcessValueOff($oldValue, $value)
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            null,
            'default'
        )->will(
            $this->returnValue($oldValue)
        );

        $this->model->setValue($value);

        $this->indexerStateMock->expects($this->never())->method('loadByIndexer');
        $this->indexerStateMock->expects($this->never())->method('setStatus');
        $this->indexerStateMock->expects($this->never())->method('save');

        $this->flatIndexerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'catalog_category_flat'
        )->will(
            $this->returnSelf()
        );
        $this->flatIndexerMock->expects($this->once())->method('setScheduled')->with(false);

        $this->model->processValue();
    }
}
