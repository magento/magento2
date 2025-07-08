<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Framework\View\Page\Layout\Reader
 */
namespace Magento\Framework\View\Test\Unit\Page\Layout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\Theme\ResolverInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\ProcessorFactory;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Layout\ReaderPool;
use Magento\Framework\View\Page\Layout\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    protected $model;

    /**
     * @var ResolverInterface|MockObject
     */
    protected $themeResolver;

    /**
     * @var ThemeInterface|MockObject
     */
    protected $themeInterface;

    /**
     * @var ProcessorFactory|MockObject
     */
    protected $processorFactory;

    /**
     * @var CollectorInterface|MockObject
     */
    protected $pageLayoutFileSource;

    /**
     * @var Context|MockObject
     */
    protected $readerContext;

    /**
     * @var ReaderPool|MockObject
     */
    protected $readerPool;

    /**
     * @var ProcessorInterface|MockObject
     */
    protected $processorInterface;

    protected function setUp(): void
    {
        $this->processorInterface = $this->getMockForAbstractClass(ProcessorInterface::class);
        $this->themeInterface = $this->getMockForAbstractClass(ThemeInterface::class);
        $this->processorFactory = $this->createPartialMock(
            ProcessorFactory::class,
            ['create']
        );
        $this->themeResolver = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->pageLayoutFileSource = $this->getMockBuilder(CollectorInterface::class)
            ->getMock();
        $this->readerPool = $this->getMockBuilder(ReaderPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerContext = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getScheduledStructure'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = (new ObjectManager($this))
            ->getObject(
                Reader::class,
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
        $this->processorInterface->expects($this->any())->method('load')->with($data)->willReturn(
            $this->processorInterface
        );
        $this->themeResolver->expects($this->atLeastOnce())->method('get')->willReturn(
            $this->themeInterface
        );
        $createData = [
            'theme' => $this->themeInterface,
            'fileSource' => $this->pageLayoutFileSource,
            'cacheSuffix' => 'page_layout',
        ];
        $this->processorFactory->expects($this->once())->method('create')
            ->with($createData)->willReturn($this->processorInterface);
        $element = new Element($xml);
        $this->processorInterface->expects($this->once())->method('asSimplexml')->willReturn($element);
        $this->readerPool->expects($this->once())->method('interpret')->with($this->readerContext, $element);
        $this->model->read($this->readerContext, $data);
    }
}
