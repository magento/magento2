<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContent\Model\GetAssetsUsedInContent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for the GetAssetUsedInContentTest command.
 */
class GetAssetsusedInContentTest extends TestCase
{
    /**
     * @var ResourceConnection | MockObject
     */
    private $resourceConnectionStub;

    /**
     * @var LoggerInterface | MockObject
     */
    private $loggerMock;

    /**
     * @var GetAssetsUsedInContent
     */
    private $getAssetsUsedInContent;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->resourceConnectionStub  = $this->createMock(ResourceConnection::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->getAssetsUsedInContent = new GetAssetsUsedInContent($this->resourceConnectionStub, $this->loggerMock);
    }

    /**
     * Test successful execution of the GetAssetsUsedInContent::execute.
     *
     * @param array $requestParameters
     * @param array $expectedAssetIdList
     * @dataProvider getAssetsListRelatedToContent
     */
    public function testSuccessfulGetUsedAssets(
        array $requestParameters,
        array $expectedAssetIdList
    ): void {
        $this->configureResourceConnectionStub($expectedAssetIdList);
        $assetList = $this->getAssetsUsedInContent->execute(
            $requestParameters['type'],
            $requestParameters['entity_id'],
            $requestParameters['field']
        );

        $this->assertEquals($expectedAssetIdList, $assetList);
    }

    /**
     * Test GetAssetsUsedInContent::execute with exception.
     */
    public function testGetUsedAssetsWithException(): void
    {
        $this->resourceConnectionStub->method('getConnection')->willThrowException((new \Exception()));
        $this->expectException(IntegrationException::class);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->willReturnSelf();

        $this->getAssetsUsedInContent->execute('cms_page', '1', 'content');
    }

    /**
     * Configure resource connection for the command. Based on the current implementation.
     *
     * @param array $expectedAssetIdList
     */
    private function configureResourceConnectionStub(array $expectedAssetIdList): void
    {
        $selectStub = $this->createMock(Select::class);
        $selectStub->expects($this->any())->method('from')->willReturnSelf();
        $selectStub->expects($this->any())->method('where')->willReturnSelf();

        $connectionMock = $this->getMockBuilder(AdapterInterface::class)->getMock();
        $connectionMock->expects($this->any())->method('select')->willReturn($selectStub);
        $connectionMock->expects($this->any())
            ->method('fetchAssoc')
            ->with($selectStub)
            ->willReturn($expectedAssetIdList);
        $this->resourceConnectionStub->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
    }

    /**
     * Media asset to media content relation data
     *
     * @return array
     */
    public function getAssetsListRelatedToContent(): array
    {
        return [
            [
                [
                    'type' => 'cms_page',
                    'entity_id' => '1',
                    'field' => 'content'
                ],
                [1234123]
            ],
            [
                [
                    'type' => 'cms_page',
                    'entity_id' => null,
                    'field' => 'content'
                ],
                [1234123, 2425168]
            ],
            [
                [
                    'type' => 'catalog_category',
                    'entity_id' => '1',
                    'field' => null
                ],
                [1234123]
            ],
            [
                [
                    'type' => 'cbm_block',
                    'entity_id' => null,
                    'field' => null
                ],
                [1234123, 2425168]
            ]
        ];
    }
}
