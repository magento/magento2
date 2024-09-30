<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Block\Term;
use Magento\Search\Model\Query;
use Magento\Search\Model\ResourceModel\Query\Collection;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Terms block
 */
class TermsTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var UrlFactory|MockObject
     */
    private $urlFactoryMock;

    /**
     * @var Term
     */
    private $termsModel;

    /**
     * @var StoreManager
     */
    private $storeManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->contextMock = $this->createMock(Context::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->urlFactoryMock = $this->createMock(UrlFactory::class);
        $this->storeManagerMock = $this->createMock(StoreManager::class);

        $this->contextMock->expects($this->once())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);
        $this->termsModel = $objectManager->getObject(
            Term::class,
            [
                'context' => $this->contextMock,
                '_queryCollectionFactory' => $this->collectionFactoryMock,
                '_urlFactory' => $this->urlFactoryMock
            ]
        );
    }

    /**
     * Verify terms
     *
     * @dataProvider termKeysProvider
     * @param string $termKey
     * @param bool $popularity
     */
    public function testGetTerms(string $termKey, bool $popularity): void
    {
        $terms = $this->createMock(Collection::class);
        $dataObjectMock = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPopularity'])
            ->onlyMethods(['getQueryText'])
            ->getMock();
        $storeMock = $this->createMock(Store::class);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($terms);
        $terms->expects($this->once())
            ->method('setPopularQueryFilter')
            ->willReturnSelf();
        $terms->expects($this->once())
            ->method('setPageSize')
            ->willReturnSelf();
        $terms->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $terms->expects($this->once())
            ->method('getItems')
            ->willReturn([$dataObjectMock]);
        $dataObjectMock->expects($this->exactly(!$popularity ? 3 : 4))
            ->method('getPopularity')
            ->willReturn($popularity);
        $dataObjectMock->expects($this->exactly(!$popularity ? 0 : 2))
            ->method('getQueryText')
            ->willReturn($termKey);

        $this->assertEquals(!$popularity ? [] : [$termKey => $dataObjectMock], $this->termsModel->getTerms());
    }

    /**
     * Verify get search Url
     *
     * @return void
     */
    public function testGetSearchResult(): void
    {
        $urlMock = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setQueryParam', 'getUrl'])
            ->getMock();

        $dataObjectMock = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPopularity'])
            ->onlyMethods(['getQueryText'])
            ->getMock();
        $this->urlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($urlMock);
        $dataObjectMock->expects($this->once())
            ->method('getQueryText')
            ->willReturn('url');
        $urlMock->expects($this->once())->method('setQueryParam');
        $urlMock->expects($this->once())
            ->method('getUrl')
            ->with('catalogsearch/result')
            ->willReturn('url');

        $this->assertEquals('url', $this->termsModel->getSearchUrl($dataObjectMock));
    }

    /**
     * Terms data key provider
     *
     * @return array
     */
    public static function termKeysProvider(): array
    {
        return [
            [
                'search',
                true
            ],
            [
                '',
                false
            ]
        ];
    }
}
