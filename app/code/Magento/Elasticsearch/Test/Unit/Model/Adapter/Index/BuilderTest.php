<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Adapter\Index\Builder;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Search\Model\ResourceModel\SynonymReader;
use Magento\Framework\DB\Adapter\AdapterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    protected $model;

    /**
     * @var LocaleResolver|MockObject
     */
    protected $localeResolver;

    /**
     * @var EsConfigInterface|MockObject
     */
    protected $esConfig;

    /**
     * @var SynonymReader|MockObject
     */
    private $synonymReaderMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * Setup method
     * @return void
     */
    protected function setUp(): void
    {
        $this->localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'emulate',
                'getLocale'
            ])
            ->getMock();

        $this->esConfig = $this->getMockBuilder(
            EsConfigInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->synonymReaderMock = $this->getMockBuilder(
            SynonymReader::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(
            AdapterInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectMock = $this->getMockBuilder(
            Select::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            Builder::class,
            [
                'localeResolver' => $this->localeResolver,
                'esConfig' => $this->esConfig,
                'synonymReader' => $this->synonymReaderMock
            ]
        );
    }

    /**
     * Test build() method
     *
     * @param string $locale
     * @dataProvider buildDataProvider
     */
    public function testBuild($locale)
    {
        $synonymsArray = [
            'mp3,player,sound,audio',
            'tv,video,television,screen'
        ];

        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn($locale);

        $this->esConfig->expects($this->once())
            ->method('getStemmerInfo')
            ->willReturn([
                'type' => 'stemmer',
                'default' => 'english',
                'en_US' => 'english',
            ]);

        $this->synonymReaderMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->willReturn($this->selectMock);

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->willReturn($synonymsArray);

        $result = $this->model->build();
        $this->assertNotNull($result);
    }

    /**
     * Test setStoreId() method
     */
    public function testSetStoreId()
    {
        $result = $this->model->setStoreId(1);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            ['en_US'],
            ['de_DE'],
        ];
    }
}
