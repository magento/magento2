<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Page;

use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;

class PostDataProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Stdlib\DateTime\Filter\Date|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateFilter;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManager;

    /** @var \Magento\Framework\View\Model\Layout\Update\ValidatorFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $validatorFactory;

    /** @var PostDataProcessor */
    protected $postDataProcessor;

    public function setUp()
    {
        $this->dateFilter = $this->getMock('Magento\Framework\Stdlib\DateTime\Filter\Date', [], [], '', false);
        $this->messageManager = $this->getMockForAbstractClass(
            'Magento\Framework\Message\ManagerInterface',
            [],
            '',
            false
        );
        $this->validatorFactory = $this->getMock(
            'Magento\Framework\View\Model\Layout\Update\ValidatorFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->postDataProcessor = new PostDataProcessor(
            $this->dateFilter,
            $this->messageManager,
            $this->validatorFactory
        );
    }

    public function testValidateRequireEntry()
    {
        $postData = [
            'title' => ''
        ];
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with(__('To apply changes you should fill in hidden required "%1" field', 'Page Title'));

        $this->assertFalse($this->postDataProcessor->validateRequireEntry($postData));
    }
}
