<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\View\Page\Layout\Reader
 */
namespace Magento\Framework\View\Test\Unit\Page\Layout;

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
     * @var \Magento\Framework\View\Layout\ReaderPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerPool;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorInterface;

    protected function setUp()
    {
        $this->processorInterface = $this->getMock(
            \Magento\Framework\View\Layout\ProcessorInterface::class,
            [],
            [],
            '',
            false
        );
        $this->themeInterface = $this->getMock(\Magento\Framework\View\Design\ThemeInterface::class, [], [], '', false);
        $this->processorFactory = $this->getMock(
            \Magento\Framework\View\Layout\ProcessorFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->themeResolver = $this->getMock(
            \Magento\Framework\View\Design\Theme\ResolverInterface::class,
            [],
            [],
            '',
            false
        );
        $this->pageLayoutFileSource = $this->getMockBuilder(\Magento\Framework\View\File\CollectorInterface::class)
            ->getMock();
        $this->readerPool = $this->getMockBuilder(\Magento\Framework\View\Layout\ReaderPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerContext = $this->getMockBuilder(\Magento\Framework\View\Layout\Reader\Context::class)
            ->setMethods(['getScheduledStructure'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Framework\View\Page\Layout\Reader::class,
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
            'cacheSuffix' => 'page_layout',
        ];
        $this->processorFactory->expects($this->once())->method('create')
            ->with($createData)->will($this->returnValue($this->processorInterface));
        $element = new \Magento\Framework\View\Layout\Element($xml);
        $this->processorInterface->expects($this->once())->method('asSimplexml')->will($this->returnValue($element));
        $this->readerPool->expects($this->once())->method('interpret')->with($this->readerContext, $element);
        $this->model->read($this->readerContext, $data);
    }
}
