<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Preprocessor;

use Magento\Elasticsearch\SearchAdapter\Query\Preprocessor\Stopwords as StopwordsPreprocessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Elasticsearch\SearchAdapter\Query\Preprocessor\Stopwords;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StopwordsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StopwordsPreprocessor
     */
    protected $model;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var LocaleResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolver;

    /**
     * @var ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readFactory;

    /**
     * @var ConfigCache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configCache;

    /**
     * @var EsConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $esConfig;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'emulate',
                'getLocale',
            ])
            ->getMock();
        $this->readFactory = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->configCache = $this->getMockBuilder(\Magento\Framework\App\Cache\Type\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->esConfig = $this->getMockBuilder(
            \Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->serializerMock = $this->getMock(SerializerInterface::class);

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
        $storeInterface = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeInterface);
        $storeInterface->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');

        $read = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Read::class)
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
        $storeInterface = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeInterface);
        $storeInterface->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');

        $read = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Read::class)
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
