<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\Test\Unit\Setup;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Elasticsearch\Setup\Validator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Elasticsearch7\Model\Client\Elasticsearch;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var ClientResolver|MockObject
     */
    private $clientResolverMock;

    /**
     * @var Elasticsearch|MockObject
     */
    private $elasticsearchClientMock;

    protected function setUp(): void
    {
        $this->clientResolverMock = $this->getMockBuilder(ClientResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->elasticsearchClientMock = $this->getMockBuilder(Elasticsearch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(
            Validator::class,
            [
                'clientResolver' => $this->clientResolverMock
            ]
        );
    }

    public function testValidate()
    {
        $this->clientResolverMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->elasticsearchClientMock);
        $this->elasticsearchClientMock->expects($this->once())->method('testConnection')->willReturn(true);

        $this->assertEquals([], $this->validator->validate());
    }

    public function testValidateFail()
    {
        $this->clientResolverMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->elasticsearchClientMock);
        $this->elasticsearchClientMock->expects($this->once())->method('testConnection')->willReturn(false);

        $expected = [
            'Could not validate a connection to Elasticsearch.'
            . ' Verify that the Elasticsearch host and port are configured correctly.'
        ];
        $this->assertEquals($expected, $this->validator->validate());
    }

    public function testValidateFailException()
    {
        $this->clientResolverMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->elasticsearchClientMock);
        $exceptionMessage = 'Could not ping host.';
        $this->elasticsearchClientMock
            ->expects($this->once())
            ->method('testConnection')
            ->willThrowException(new \Exception($exceptionMessage));

        $expected = ['Could not validate a connection to Elasticsearch. ' . $exceptionMessage];
        $this->assertEquals($expected, $this->validator->validate());
    }
}
