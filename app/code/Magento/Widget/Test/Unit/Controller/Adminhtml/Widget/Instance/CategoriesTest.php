<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Unit\Controller\Adminhtml\Widget\Instance;

class CategoriesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $chooser;

    /**
     * @var string
     */
    protected $blockClass = \Magento\Widget\Block\Adminhtml\Widget\Catalog\Category\Chooser::class;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRaw;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Widget\Controller\Adminhtml\Widget\Instance\Categories
     */
    protected $controller;

    protected function setUp()
    {
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->mathRandom = $this->createMock(\Magento\Framework\Math\Random::class);
        $this->chooser = $this->createPartialMock(
            $this->blockClass,
            ['setUseMassaction', 'setId', 'setIsAnchorOnly', 'setSelectedCategories', 'toHtml']
        );
        $this->layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->resultRaw = $this->createMock(\Magento\Framework\Controller\Result\Raw::class);
        $this->resultFactory = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $this->context = $this->createMock(\Magento\Backend\App\Action\Context::class);
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
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_RAW)
            ->willReturn($this->resultRaw);

        $this->context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->once())->method('getResultFactory')->willReturn($this->resultFactory);

        /** @var \Magento\Widget\Controller\Adminhtml\Widget\Instance\Categories $controller */
        $this->controller = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Widget\Controller\Adminhtml\Widget\Instance\Categories::class,
                [
                    'context' => $this->context,
                    'mathRandom' => $this->mathRandom,
                    'layout' => $this->layout
                ]
            );
        $this->assertSame($this->resultRaw, $this->controller->execute());
    }
}
