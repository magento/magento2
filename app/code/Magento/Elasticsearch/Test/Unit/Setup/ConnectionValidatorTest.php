<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Setup;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Elasticsearch\Setup\ConnectionValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Elasticsearch\Elasticsearch5\Model\Client\Elasticsearch;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConnectionValidatorTest extends TestCase
{
    /**
     * @var ConnectionValidator
     */
    private $connectionValidator;

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
        $this->connectionValidator = $objectManager->getObject(
            ConnectionValidator::class,
            [
                'clientResolver' => $this->clientResolverMock
            ]
        );
    }

    public function testValidate()
    {
        $searchEngine = 'elasticsearch5';

        $this->clientResolverMock
            ->expects($this->once())
            ->method('create')
            ->with($searchEngine)
            ->willReturn($this->elasticsearchClientMock);
        $this->elasticsearchClientMock->expects($this->once())->method('testConnection')->willReturn(true);

        $this->assertTrue($this->connectionValidator->validate($searchEngine));
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

        $this->assertFalse($this->connectionValidator->validate($searchEngine));
    }
}
