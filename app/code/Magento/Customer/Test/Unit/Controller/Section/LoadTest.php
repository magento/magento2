<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Section;

use Magento\Customer\Controller\Section\Load;
use Magento\Customer\CustomerData\Section\Identifier;
use Magento\Customer\CustomerData\SectionPoolInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use \PHPUnit\Framework\MockObject\MockObject as MockObject;
use Magento\Framework\App\Request\Http as HttpRequest;

class LoadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Load
     */
    private $loadAction;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var Identifier|MockObject
     */
    private $sectionIdentifierMock;

    /**
     * @var SectionPoolInterface|MockObject
     */
    private $sectionPoolMock;

    /**
     * @var \Magento\Framework\Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var Json|MockObject
     */
    private $resultJsonMock;

    /**
     * @var HttpRequest|MockObject
     */
    private $httpRequestMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->resultJsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->sectionIdentifierMock = $this->createMock(Identifier::class);
        $this->sectionPoolMock = $this->getMockForAbstractClass(SectionPoolInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->httpRequestMock = $this->createMock(HttpRequest::class);
        $this->resultJsonMock = $this->createMock(Json::class);

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->httpRequestMock);

        $this->loadAction = new Load(
            $this->contextMock,
            $this->resultJsonFactoryMock,
            $this->sectionIdentifierMock,
            $this->sectionPoolMock,
            $this->escaperMock
        );
    }

    /**
     * @param string $sectionNames
     * @param bool $forceNewSectionTimestamp
     * @param string[] $sectionNamesAsArray
     * @param bool $forceNewTimestamp
     * @dataProvider executeDataProvider
     */
    public function testExecute($sectionNames, $forceNewSectionTimestamp, $sectionNamesAsArray, $forceNewTimestamp)
    {
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->exactly(2))
            ->method('setHeader')
            ->withConsecutive(
                ['Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store'],
                ['Pragma', 'no-cache']
            );

        $this->httpRequestMock->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(['sections'], ['force_new_section_timestamp'])
            ->willReturnOnConsecutiveCalls($sectionNames, $forceNewSectionTimestamp);

        $this->sectionPoolMock->expects($this->once())
            ->method('getSectionsData')
            ->with($sectionNamesAsArray, $forceNewTimestamp)
            ->willReturn([
                'message' => 'some message',
                'someKey' => 'someValue'
            ]);

        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with([
                'message' => 'some message',
                'someKey' => 'someValue'
            ])
            ->willReturn($this->resultJsonMock);

        $this->loadAction->execute();
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                'sectionNames' => 'sectionName1,sectionName2,sectionName3',
                'forceNewSectionTimestamp' => 'forceNewSectionTimestamp',
                'sectionNamesAsArray' => ['sectionName1', 'sectionName2', 'sectionName3'],
                'forceNewTimestamp' => true
            ],
            [
                'sectionNames' => null,
                'forceNewSectionTimestamp' => null,
                'sectionNamesAsArray' => null,
                'forceNewTimestamp' => false
            ],
        ];
    }

    public function testExecuteWithThrowException()
    {
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->exactly(2))
            ->method('setHeader')
            ->withConsecutive(
                ['Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store'],
                ['Pragma', 'no-cache']
            );

        $this->httpRequestMock->expects($this->once())
            ->method('getParam')
            ->with('sections')
            ->willThrowException(new \Exception('Some Message'));

        $this->resultJsonMock->expects($this->once())
            ->method('setStatusHeader')
            ->with(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );

        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with('Some Message')
            ->willReturn('Some Message');

        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(['message' => 'Some Message'])
            ->willReturn($this->resultJsonMock);

        $this->loadAction->execute();
    }
}
