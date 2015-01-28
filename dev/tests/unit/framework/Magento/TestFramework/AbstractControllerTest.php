<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework;

class AbstractControllerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\App\Action\Context | \PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var array */
    private $containedObjects;

    /** @var array */
    private $containedTypes;

    /**
     * @param array $providedObjects Array of mocks that the context object should return
     *      format: ['objectName' => instance of PHPUnit_Framework_MockObject_MockObject]
     */
    protected function setUpContext($providedObjects)
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->containedTypes = [
            'request' => 'Magento\Framework\App\RequestInterface',
            'response' => 'Magento\Framework\App\ResponseInterface',
            'objectManager' => 'Magento\Framework\ObjectManagerInterface',
            'eventManager' => 'Magento\Framework\Event\ManagerInterface',
            'url' => 'Magento\Framework\UrlInterface',
            'redirect' => 'Magento\Framework\App\Response\RedirectInterface',
            'actionFlag' => 'Magento\Framework\App\ActionFlag',
            'view' => 'Magento\Framework\App\ViewInterface',
            'messageManager' => 'Magento\Framework\Message\ManagerInterface',
            'designLoader' => 'Magento\Framework\View\DesignLoader',
        ];

        foreach ($this->containedTypes as $mockName => $mock) {
            if (isset($providedObjects[$mockName])) {
                $this->containedObjects[$mockName] = $providedObjects[$mockName];
            } else {
                $this->containedObjects[$mockName] = null;
            }
        }

        foreach ($this->containedObjects as $mockName => $mock) {
            if (is_null($mock)) {
                $this->containedObjects[$mockName] = $this->getMockBuilder($this->containedTypes[$mockName])
                    ->disableOriginalConstructor()
                    ->getMock();
            }
        }

        foreach ($this->containedObjects as $mockName => $mock) {
            $this->contextMock
                ->expects($this->once())
                ->method('get' . ucfirst($mockName))
                ->willReturn($mock);
        }
    }
}
