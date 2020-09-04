<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Preprocessor;

use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;
use Magento\Elasticsearch\SearchAdapter\Query\Preprocessor\Stopwords;
use Magento\Elasticsearch\SearchAdapter\Query\Preprocessor\Stopwords as StopwordsPreprocessor;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StopwordsTest extends TestCase
{
    /**
     * @var StopwordsPreprocessor
     */
    protected $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var LocaleResolver|MockObject
     */
    protected $localeResolver;

    /**
     * @var ReadFactory|MockObject
     */
    protected $readFactory;

    /**
     * @var ConfigCache|MockObject
     */
    protected $configCache;

    /**
     * @var EsConfigInterface|MockObject
     */
    protected $esConfig;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'emulate',
                'getLocale',
            ])
            ->getMock();
        $this->readFactory = $this->getMockBuilder(ReadFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->configCache = $this->getMockBuilder(\Magento\Framework\App\Cache\Type\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->esConfig = $this->getMockBuilder(
            EsConfigInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            Stopwords::class,
            [
                'storeManager' => $this->storeManager,
                'localeResolver' => $this->localeResolver,
                'readFactory' => $this->readFactory,
                'configCache' => $this->configCache,
                'esConfig' => $this->esConfig,
                'stopwordsModule' => '',
                'stopwordsDirectory' => ''
            ]
        );
        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'serializer',
            $this->serializerMock
        );
    }

    /**
     * Test process() method
     */
    public function testProcess()
    {
        $stopWordsFromFile = "a\nthe\nof";
        $stopWords = ['a', 'the', 'of'];
        $serializedStopWords = 'serialized stop words';
        $this->esConfig->expects($this->once())
            ->method('getStopwordsInfo')
            ->willReturn([
                'default' => 'default.csv',
            ]);
        $storeInterface = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeInterface);
        $storeInterface->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');

        $read = $this->getMockBuilder(Read::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readFactory->expects($this->once())
            ->method('create')
            ->willReturn($read);
        $read->expects($this->once())
            ->method('stat')
            ->willReturn([
                'mtime' => 0,
            ]);
        $this->configCache->expects($this->once())
            ->method('load')
            ->willReturn(false);

        $read->expects($this->once())
            ->method('readFile')
            ->willReturn($stopWordsFromFile);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($stopWords)
            ->willReturn($serializedStopWords);
        $this->configCache->expects($this->once())
            ->method('save')
            ->with(
                $serializedStopWords,
                Stopwords::CACHE_ID
            );

        $this->assertEquals(
            'test query',
            $this->model->process('the test of a query')
        );
    }

    /**
     * Test process() method, load stopwords from cache
     */
    public function testProcessFromCache()
    {
        $serializedStopWords = 'serialized stop words';
        $this->esConfig->expects($this->once())
            ->method('getStopwordsInfo')
            ->willReturn([
                'default' => 'default.csv',
            ]);
        $storeInterface = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeInterface);
        $storeInterface->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');

        $read = $this->getMockBuilder(Read::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readFactory->expects($this->once())
            ->method('create')
            ->willReturn($read);
        $read->expects($this->once())
            ->method('stat')
            ->willReturn([
                'mtime' => 0,
            ]);
        $this->configCache->expects($this->once())
            ->method('load')
            ->willReturn($serializedStopWords);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedStopWords)
            ->willReturn(['a', 'the', 'of']);

        $this->assertEquals(
            'test query',
            $this->model->process('the test of a query')
        );
    }
}
