<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Setup;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Elasticsearch\Setup\Validator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Elasticsearch\Elasticsearch5\Model\Client\Elasticsearch;
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

    protected function setUp()
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
            ->with(null)
            ->willReturn($this->elasticsearchClientMock);
        $this->elasticsearchClientMock->expects($this->once())->method('testConnection')->willReturn(true);

        $this->assertEquals([], $this->validator->validate());
    }

    public function testValidateFail()
    {
        $searchEngine = 'elasticsearch5';

        $this->clientResolverMock
            ->expects($this->once())
            ->method('create')
            ->with($searchEngine)
            ->willReturn($this->elasticsearchClientMock);
        $this->elasticsearchClientMock->expects($this->once())->method('testConnection')->willReturn(false);

        $expected = ['Elasticsearch connection validation failed'];
        $this->assertEquals($expected, $this->validator->validate(['search-engine' => $searchEngine]));
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

        $expected = ['Elasticsearch connection validation failed: ' . $exceptionMessage];
        $this->assertEquals($expected, $this->validator->validate());
    }
}
