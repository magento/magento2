<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Controller\Adminhtml\Synonyms;

/**
 * Unit tests for Magento\Search\Controller\Adminhtml\Synonyms\MassDelete controller.
 */
class MassDeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Search\Controller\Adminhtml\Synonyms\MassDelete
     */
    private $controller;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var \Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \Magento\Search\Api\SynonymGroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $synGroupRepositoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['isPost']
        );
        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->filterMock = $this->createMock(\Magento\Ui\Component\MassAction\Filter::class);
        $this->collectionFactoryMock = $this->createMock(
            \Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory::class
        );
        $this->synGroupRepositoryMock = $this->createMock(\Magento\Search\Api\SynonymGroupRepositoryInterface::class);

        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);

        $this->controller = $objectManagerHelper->getObject(
            \Magento\Search\Controller\Adminhtml\Synonyms\MassDelete::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'synGroupRepository' => $this->synGroupRepositoryMock,
            ]
        );
    }

    /**
     * Check that error throws when request is not POST.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteWithNotPostRequest()
    {
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(false);

        $this->controller->execute();
    }
}
