<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeResolver = $this->getMockBuilder('Magento\Framework\Locale\Resolver')
            ->disableOriginalConstructor()
            ->setMethods([
                'emulate',
                'getLocale',
            ])
            ->getMock();
        $this->readFactory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->configCache = $this->getMockBuilder('Magento\Framework\App\Cache\Type\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->esConfig = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Elasticsearch\SearchAdapter\Query\Preprocessor\Stopwords',
            [
                'storeManager' => $this->storeManager,
                'localeResolver' => $this->localeResolver,
                'readFactory' => $this->readFactory,
                'configCache' => $this->configCache,
                'esConfig' => $this->esConfig,
                'fileDir' => '',
            ]
        );
    }

    /**
     * Test process() method
     */
    public function testProcess()
    {
        $this->esConfig->expects($this->once())
            ->method('getStopwordsInfo')
            ->willReturn([
                'default' => 'default.csv',
            ]);
        $storeInterface = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
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

        $read = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\Read')
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
            ->willReturn('');

        $read->expects($this->once())
            ->method('readFile')
            ->willReturn("a\nthe\nof");

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
        $this->esConfig->expects($this->once())
            ->method('getStopwordsInfo')
            ->willReturn([
                'default' => 'default.csv',
            ]);
        $storeInterface = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
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

        $read = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\Read')
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
            ->willReturn('a:3:{i:0;s:1:"a";i:1;s:3:"the";i:2;s:2:"of";}');

        $this->assertEquals(
            'test query',
            $this->model->process('the test of a query')
        );
    }
}
