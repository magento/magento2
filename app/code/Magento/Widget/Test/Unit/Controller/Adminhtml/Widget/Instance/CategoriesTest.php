<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Controller\Adminhtml\Widget\Instance;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Widget\Block\Adminhtml\Widget\Catalog\Category\Chooser;
use Magento\Widget\Controller\Adminhtml\Widget\Instance\Categories;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoriesTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var Random|MockObject
     */
    protected $mathRandom;

    /**
     * @var Layout|MockObject
     */
    protected $chooser;

    /**
     * @var string
     */
    protected $blockClass = Chooser::class;

    /**
     * @var Layout|MockObject
     */
    protected $layout;

    /**
     * @var Raw|MockObject
     */
    protected $resultRaw;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Categories
     */
    protected $controller;

    protected function setUp(): void
    {
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->mathRandom = $this->createMock(Random::class);
        $this->chooser = $this->getMockBuilder($this->blockClass)
            ->disableOriginalConstructor()
            ->addMethods(['setUseMassaction', 'setId', 'setIsAnchorOnly'])
            ->onlyMethods(['setSelectedCategories', 'toHtml'])
            ->getMock();
        $this->layout = $this->createMock(Layout::class);
        $this->resultRaw = $this->createMock(Raw::class);
        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->context = $this->createMock(Context::class);
    }

    public function testExecute()
    {
        $selectedCategories = '1';
        $isAnchorOnly = true;
        $hash = '7e6baeca2d76ca0efc3a299986d31bdc9cd796fb';
        $content = 'block_content';

        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['selected', '', $selectedCategories],
                ['is_anchor_only', 0, $isAnchorOnly]
            ]
        );

        $this->mathRandom->expects($this->once())->method('getUniqueHash')->with('categories')->willReturn($hash);

        $this->chooser->expects($this->once())->method('setUseMassaction')->with()->willReturnSelf();
        $this->chooser->expects($this->once())->method('setId')->with($hash)->willReturnSelf();
        $this->chooser->expects($this->once())->method('setIsAnchorOnly')->with($isAnchorOnly)->willReturnSelf();
        $this->chooser->expects($this->once())
            ->method('setSelectedCategories')
            ->with(explode(',', $selectedCategories))
            ->willReturnSelf();
        $this->chooser->expects($this->once())->method('toHtml')->willReturn($content);

        $this->layout->expects($this->once())
            ->method('createBlock')
            ->with($this->blockClass)
            ->willReturn($this->chooser);

        $this->resultRaw->expects($this->once())->method('setContents')->with($content)->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_RAW)
            ->willReturn($this->resultRaw);

        $this->context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->once())->method('getResultFactory')->willReturn($this->resultFactory);

        /** @var Categories $controller */
        $this->controller = (new ObjectManager($this))
            ->getObject(
                Categories::class,
                [
                    'context' => $this->context,
                    'mathRandom' => $this->mathRandom,
                    'layout' => $this->layout
                ]
            );
        $this->assertSame($this->resultRaw, $this->controller->execute());
    }
}
