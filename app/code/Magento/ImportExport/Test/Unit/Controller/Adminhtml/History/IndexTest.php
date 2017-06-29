<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\History;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\ImportExport\Controller\Adminhtml\History\Index
     */
    protected $indexController;

    /**
     * @var \Magento\Framework\Controller\ResultFactory||\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    protected $resultPage;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->resultPage = $this->getMock(
            \Magento\Backend\Model\View\Result\Page::class,
            ['setActiveMenu', 'getConfig', 'getTitle', 'prepend', 'addBreadcrumb'],
            [],
            '',
            false
        );
        $this->resultPage->expects($this->any())->method('getConfig')->willReturnSelf();
        $this->resultPage->expects($this->any())->method('getTitle')->willReturnSelf();
        $this->resultFactory = $this->getMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resultFactory->expects($this->any())->method('create')->willReturn($this->resultPage);
        $this->context = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            ['getResultFactory'],
            [],
            '',
            false
        );
        $this->context->expects($this->any())->method('getResultFactory')->willReturn($this->resultFactory);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->indexController = $this->objectManagerHelper->getObject(
            \Magento\ImportExport\Controller\Adminhtml\History\Index::class,
            [
                'context' => $this->context,
            ]
        );
    }

    /**
     * Test execute
     */
    public function testExecute()
    {
        $this->indexController->execute();
    }
}
