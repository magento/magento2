<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Model\Layout;

use Magento\Framework\App\State;
use Magento\Framework\Config\Dom\ValidationSchemaException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class MergeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Model\Layout\Merge
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\Url\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scope;

    /**
     * @var \Magento\Framework\View\Model\Layout\Update\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutValidator;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appState;

    /**
     * @var \Magento\Framework\View\Layout\LayoutCacheKeyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutCacheKeyMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->scope = $this->getMockForAbstractClass(\Magento\Framework\Url\ScopeInterface::class);
        $this->layoutValidator = $this->getMockBuilder(\Magento\Framework\View\Model\Layout\Update\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);
        $this->appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutCacheKeyMock = $this->getMockForAbstractClass(\Magento\Framework\View\Layout\LayoutCacheKeyInterface::class);
        $this->layoutCacheKeyMock->expects($this->any())
            ->method('getCacheKeys')
            ->willReturn([]);

        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Model\Layout\Merge::class,
            [
                'scope' => $this->scope,
                'layoutValidator' => $this->layoutValidator,
                'logger' => $this->logger,
                'appState' => $this->appState,
                'layoutCacheKey' => $this->layoutCacheKeyMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Config\Dom\ValidationSchemaException
     * @expectedExceptionMessage Processed schema file is not valid.
     */
    public function testValidateMergedLayoutThrowsException()
    {
        $messages = [
            'Please correct the XSD data and try again.',
        ];
        $this->scope->expects($this->once())->method('getId')->willReturn(1);
        $this->layoutValidator->expects($this->once())
            ->method('isValid')
            ->willThrowException(
                new ValidationSchemaException(
                    new Phrase('Processed schema file is not valid.')
                )
            );
        $this->layoutValidator->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);
        $this->appState->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->model->load();
    }
}
