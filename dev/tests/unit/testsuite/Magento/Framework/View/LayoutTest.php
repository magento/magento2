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
namespace Magento\Framework\View;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_structureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_blockFactoryMock;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorFactoryMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appStateMock;

    /**
     * @var \Magento\Framework\View\Design\Theme\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeResolverMock;

    /**
     * @var \Magento\Core\Model\Layout\Merge|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorMock;

    protected function setUp()
    {
        $this->_structureMock = $this->getMockBuilder(
            'Magento\Framework\Data\Structure'
        )->setMethods(
            array('createElement')
        )->disableOriginalConstructor()->getMock();
        $this->_blockFactoryMock = $this->getMockBuilder(
            'Magento\Framework\View\Element\BlockFactory'
        )->setMethods(
            array('createBlock')
        )->disableOriginalConstructor()->getMock();
        $this->processorFactoryMock = $this->getMock(
            'Magento\Framework\View\Layout\ProcessorFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->appStateMock = $this->getMock(
            'Magento\Framework\App\State',
            [],
            [],
            '',
            false
        );
        $this->themeResolverMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\Theme\ResolverInterface'
        );
        $this->processorMock = $this->getMock(
            'Magento\Core\Model\Layout\Merge',
            ['__destruct'],
            [],
            '',
            false
        );

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            'Magento\Framework\View\Layout',
            array(
                'structure' => $this->_structureMock,
                'blockFactory' => $this->_blockFactoryMock,
                'themeResolver' => $this->themeResolverMock,
                'processorFactory' => $this->processorFactoryMock,
                'appState' => $this->appStateMock,
            )
        );
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testCreateBlockException()
    {
        $this->_model->createBlock('type', 'blockname', array());
    }

    public function testCreateBlockSuccess()
    {
        $blockMock = $this->getMockBuilder(
            'Magento\Framework\View\Element\AbstractBlock'
        )->disableOriginalConstructor()->getMockForAbstractClass();
        $this->_blockFactoryMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));

        $this->_model->createBlock('type', 'blockname', array());
        $this->assertInstanceOf('Magento\Framework\View\Element\AbstractBlock', $this->_model->getBlock('blockname'));
    }

    public function testGetUpdate()
    {
        $themeMock = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');

        $this->themeResolverMock->expects(
            $this->once()
        )->method(
            'get'
        )->will(
            $this->returnValue($themeMock)
        );

        $this->processorFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            array('theme' => $themeMock)
        )->will(
            $this->returnValue($this->processorMock)
        );

        $this->assertEquals($this->processorMock, $this->_model->getUpdate());
        $this->assertEquals($this->processorMock, $this->_model->getUpdate());
    }
}
