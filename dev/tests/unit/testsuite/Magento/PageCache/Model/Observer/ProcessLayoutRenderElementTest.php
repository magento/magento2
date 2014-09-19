<?php
/**
 *
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
namespace Magento\PageCache\Model\Observer;

class ProcessLayoutRenderElementTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Model\Observer\ProcessLayoutRenderElement */
    protected $_model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Model\Config */
    protected $_configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Element\AbstractBlock */
    protected $_blockMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Helper\Data */
    protected $_helperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Layout */
    protected $_layoutMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event\Observer */
    protected $_observerMock;

    /** @var \Magento\Framework\Object */
    protected $_transport;

    /**
     * Set up all mocks and data for test
     */
    public function setUp()
    {
        $this->_configMock = $this->getMock(
            'Magento\PageCache\Model\Config',
            array('getType', 'isEnabled'),
            array(),
            '',
            false
        );
        $this->_helperMock = $this->getMock('Magento\PageCache\Helper\Data', array(), array(), '', false);

        $this->_model = new \Magento\PageCache\Model\Observer\ProcessLayoutRenderElement(
            $this->_configMock,
            $this->_helperMock
        );
        $this->_observerMock = $this->getMock(
            'Magento\Framework\Event\Observer',
            array('getEvent'),
            array(),
            '',
            false
        );
        $this->_layoutMock = $this->getMock(
            'Magento\Framework\View\Layout',
            array('isCacheable', 'getBlock', 'getUpdate', 'getHandles'),
            array(),
            '',
            false
        );
        $this->_blockMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\AbstractBlock',
            array(),
            '',
            false,
            true,
            true,
            array('getData', 'isScopePrivate', 'getNameInLayout', 'getUrl')
        );
        $this->_transport = new \Magento\Framework\Object(array('output' => 'test output html'));
    }

    /**
     * @param bool $cacheState
     * @param bool $varnishIsEnabled
     * @param bool $scopeIsPrivate
     * @param int|null $blockTtl
     * @param string $expectedOutput
     * @dataProvider processLayoutRenderDataProvider
     */
    public function testExecute(
        $cacheState,
        $varnishIsEnabled,
        $scopeIsPrivate,
        $blockTtl,
        $expectedOutput
    ) {
        $eventMock = $this->getMock(
            'Magento\Framework\Event',
            array('getLayout', 'getElementName', 'getTransport'),
            array(),
            '',
            false
        );
        $this->_observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
        $eventMock->expects($this->once())->method('getLayout')->will($this->returnValue($this->_layoutMock));
        $this->_configMock->expects($this->any())->method('isEnabled')->will($this->returnValue($cacheState));

        if ($cacheState) {
            $eventMock->expects($this->once())->method('getElementName')->will($this->returnValue('blockName'));
            $eventMock->expects($this->once())->method('getTransport')->will($this->returnValue($this->_transport));
            $this->_layoutMock->expects($this->once())->method('isCacheable')->will($this->returnValue(true));

            $this->_layoutMock->expects($this->any())->method('getUpdate')->will($this->returnSelf());
            $this->_layoutMock->expects($this->any())->method('getHandles')->will($this->returnValue(array()));
            $this->_layoutMock->expects(
                $this->once()
            )->method(
                    'getBlock'
                )->will(
                    $this->returnValue($this->_blockMock)
                );

            if ($varnishIsEnabled) {
                $this->_blockMock->expects($this->once())
                    ->method('getData')
                    ->with('ttl')
                    ->will($this->returnValue($blockTtl));
                $this->_blockMock->expects($this->any())
                    ->method('getUrl')
                    ->will($this->returnValue('page_cache/block/wrapesi/with/handles/and/other/stuff'));
            }
            if ($scopeIsPrivate) {
                $this->_blockMock->expects(
                    $this->once()
                )->method(
                        'getNameInLayout'
                    )->will(
                        $this->returnValue('testBlockName')
                    );
                $this->_blockMock->expects(
                    $this->once()
                )->method(
                        'isScopePrivate'
                    )->will(
                        $this->returnValue($scopeIsPrivate)
                    );
            }
            $this->_configMock->expects($this->any())->method('getType')->will($this->returnValue($varnishIsEnabled));
        }
        $this->_model->execute($this->_observerMock);

        $this->assertEquals($expectedOutput, $this->_transport['output']);
    }

    /**
     * Data provider for testProcessLayoutRenderElement
     *
     * @return array
     */
    public function processLayoutRenderDataProvider()
    {
        return array(
            'full_page type and Varnish enabled, public scope, ttl is set' => array(
                true,
                true,
                false,
                360,
                '<esi:include src="page_cache/block/wrapesi/with/handles/and/other/stuff" />'
            ),
            'full_page type and Varnish enabled, public scope, ttl is not set' => array(
                true,
                true,
                false,
                null,
                'test output html'
            ),
            'full_page type enabled, Varnish disabled, public scope, ttl is set' => array(
                true,
                false,
                false,
                360,
                'test output html'
            ),
            'full_page type enabled, Varnish disabled, public scope, ttl is not set' => array(
                true,
                false,
                false,
                null,
                'test output html'
            ),
            'full_page type enabled, Varnish disabled, private scope, ttl is not set' => array(
                true,
                false,
                true,
                null,
                '<!-- BLOCK testBlockName -->test output html<!-- /BLOCK testBlockName -->'
            ),
            'full_page type is disabled, Varnish enabled' => array(false, true, false, null, 'test output html')
        );
    }
}
