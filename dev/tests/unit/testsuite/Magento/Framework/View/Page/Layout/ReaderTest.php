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

/**
 * Test class for \Magento\Framework\View\Page\Layout\Reader
 */
namespace Magento\Framework\View\Page\Layout;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Page\Layout\Reader
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Design\Theme\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeResolver;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeInterface;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorFactory;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageLayoutFileSource;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerContext;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerPool;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorInterface;

    public function setUp()
    {
        $this->processorInterface = $this->getMock(
            'Magento\Framework\View\Layout\ProcessorInterface',
            [],
            [],
            '',
            false
        );
        $this->themeInterface = $this->getMock('Magento\Framework\View\Design\ThemeInterface', [], [], '', false);
        $this->processorFactory = $this->getMock(
            'Magento\Framework\View\Layout\ProcessorFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->themeResolver = $this->getMock(
            'Magento\Framework\View\Design\Theme\ResolverInterface',
            [],
            [],
            '',
            false
        );
        $this->pageLayoutFileSource = $this->getMockBuilder('Magento\Framework\View\File\CollectorInterface')
            ->getMock();
        $this->readerPool = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Pool')
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerContext = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->setMethods(['getScheduledStructure'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject(
                'Magento\Framework\View\Page\Layout\Reader',
                [
                    'themeResolver' => $this->themeResolver,
                    'processorFactory' => $this->processorFactory,
                    'pageLayoutFileSource' => $this->pageLayoutFileSource,
                    'reader' => $this->readerPool
                ]
            );
    }

    public function testRead()
    {
        $data = 'test_string';
        $xml = '<body>
                    <attribute name="body_attribute_name" value="body_attribute_value" />
                </body>';
        $this->processorInterface->expects($this->any())->method('load')->with($data)->will(
            $this->returnValue($this->processorInterface)
        );
        $this->themeResolver->expects($this->atLeastOnce())->method('get')->will(
            $this->returnValue($this->themeInterface)
        );
        $createData = [
            'theme' => $this->themeInterface,
            'fileSource' => $this->pageLayoutFileSource,
            'cacheSuffix' => 'page_layout'
        ];
        $this->processorFactory->expects($this->once())->method('create')
            ->with($createData)->will($this->returnValue($this->processorInterface));
        $element = new \Magento\Framework\View\Layout\Element($xml);
        $this->processorInterface->expects($this->once())->method('asSimplexml')->will($this->returnValue($element));
        $this->readerPool->expects($this->once())->method('readStructure')->with($this->readerContext, $element);
        $this->model->read($this->readerContext, $data);
    }
}
