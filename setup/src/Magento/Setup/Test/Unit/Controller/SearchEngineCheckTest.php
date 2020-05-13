<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\Stdlib\RequestInterface;
use Laminas\View\Model\JsonModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Controller\SearchEngineCheck;
use Magento\Setup\Model\SearchConfigOptionsList;
use Magento\Setup\Validator\ElasticsearchConnectionValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SearchEngineCheckTest extends TestCase
{
    /**
     * @var SearchEngineCheck
     */
    private $controller;

    /**
     * @var ElasticsearchConnectionValidator|MockObject
     */
    private $connectionValidatorMock;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $searchConfigOptionsList = new SearchConfigOptionsList();
        $this->objectManagerHelper = new ObjectManager($this);
        $this->connectionValidatorMock = $this->getMockBuilder(ElasticsearchConnectionValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new SearchEngineCheck(
            $this->connectionValidatorMock,
            $searchConfigOptionsList
        );
    }

    public function testIndexAction()
    {
        $requestData = [
            'engine' => 'elasticsearch7',
            'elasticsearch' => [
                'hostname' => 'localhost',
                'port' => '9200',
                'timeout' => '15',
                'indexPrefix' => 'prefix',
                'enableAuth' => false,
                'username' => '',
                'password' => ''
            ]
        ];
        /** @var RequestInterface|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($requestData));
        $this->objectManagerHelper->setBackwardCompatibleProperty($this->controller, 'request', $requestMock);

        $this->connectionValidatorMock->expects($this->once())->method('isValidConnection')->willReturn(true);

        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $this->assertEquals(['success' => true], $jsonModel->getVariables());
    }

    public function testIndexActionFailure()
    {
        $requestData = [
            'engine' => 'elasticsearch7',
            'elasticsearch' => [
                'hostname' => 'other.host',
                'port' => '9200',
                'timeout' => '15',
                'indexPrefix' => 'prefix',
                'enableAuth' => false,
                'username' => '',
                'password' => ''
            ]
        ];
        /** @var RequestInterface|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($requestData));
        $this->objectManagerHelper->setBackwardCompatibleProperty($this->controller, 'request', $requestMock);

        $exceptionMessage = 'Could not connect to Elasticsearch server.';
        $this->connectionValidatorMock
            ->expects($this->once())
            ->method('isValidConnection')
            ->willThrowException(new \Exception($exceptionMessage));

        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $this->assertEquals(['success' => false, 'error' => $exceptionMessage], $jsonModel->getVariables());
    }

    public function testIndexActionInvalidEngine()
    {
        $requestData = [
            'engine' => 'other-engine',
            'elasticsearch' => [
                'hostname' => 'other.host',
                'port' => '9200',
                'timeout' => '15',
                'indexPrefix' => 'prefix',
                'enableAuth' => false,
                'username' => '',
                'password' => ''
            ]
        ];
        /** @var RequestInterface|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($requestData));
        $this->objectManagerHelper->setBackwardCompatibleProperty($this->controller, 'request', $requestMock);
        $this->connectionValidatorMock->expects($this->never())->method('isValidConnection');

        $expectedErrorMessage = 'Please select a valid search engine.';
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $this->assertEquals(['success' => false, 'error' => $expectedErrorMessage], $jsonModel->getVariables());
    }
}
