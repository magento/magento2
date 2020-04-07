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
use Magento\MediaContent\Model\GetAssetIdsUsedInContent;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
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
     * @var GetAssetIdsUsedInContent
     */
    private $getAssetsUsedInContent;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->resourceConnectionStub  = $this->createMock(ResourceConnection::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->getAssetsUsedInContent = new GetAssetIdsUsedInContent(
            $this->resourceConnectionStub,
            $this->loggerMock
        );
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
            $this->getContentIdentity(
                $requestParameters['type'],
                $requestParameters['field'],
                $requestParameters['entity_id']
            )
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

        $this->getAssetsUsedInContent->execute($this->createMock(ContentIdentityInterface::class));
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
     * Get content identity mock
     *
     * @param string $type
     * @param string $field
     * @param string $id
     * @return MockObject|ContentIdentityInterface
     */
    private function getContentIdentity(string $type, string $field, string $id): MockObject
    {
        $contentIdentity = $this->createMock(ContentIdentityInterface::class);
        $contentIdentity->expects($this->once())
            ->method('getEntityId')
            ->willReturn($id);
        $contentIdentity->expects($this->once())
            ->method('getField')
            ->willReturn($field);
        $contentIdentity->expects($this->once())
            ->method('getEntityType')
            ->willReturn($type);

        return $contentIdentity;
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
                    'entity_type' => 'cms_page',
                    'entity_id' => '1',
                    'field' => 'content'
                ],
                [1234123]
            ]
        ];
    }
}
