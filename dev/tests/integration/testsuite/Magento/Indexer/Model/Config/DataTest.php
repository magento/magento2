<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Config;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

    }

    public function testConstructor()
    {
        $pathFiles = __DIR__ . '/_files';
        $expectedResult = require $pathFiles . '/result.php';
        $path = $pathFiles . '/indexer.xml';
        $fileResolverMock = $this->getMock(\Magento\Framework\Config\FileResolverInterface::class);
        $fileIterator = $this->objectManager->create(
            \Magento\Framework\Config\FileIterator::class,
            [
                'paths' => [$path],
            ]
        );
        $fileResolverMock->method('get')
            ->willReturn($fileIterator);
        $this->cleanAllCache();

        $reader = $this->objectManager->create(
            \Magento\Framework\Indexer\Config\Reader::class,
            [
                'fileResolver' => $fileResolverMock,
            ]
        );
        $model = $this->objectManager->create(
            \Magento\Indexer\Model\Config\Data::class,
            [
                'reader' => $reader,
            ]
        );
        $this->assertEquals($expectedResult['catalogsearch_fulltext'], $model->get('catalogsearch_fulltext'));
        $model2 = $this->objectManager->create(
            \Magento\Indexer\Model\Config\Data::class,
            [
                'reader' => $reader,
            ]
        );
        $this->assertEquals($expectedResult['catalogsearch_fulltext'], $model2->get('catalogsearch_fulltext'));
    }

    private function cleanAllCache()
    {
        /** @var \Magento\Framework\App\Cache\Frontend\Pool $cachePool */
        $cachePool = $this->objectManager->get(\Magento\Framework\App\Cache\Frontend\Pool::class);
        /** @var \Magento\Framework\Cache\FrontendInterface $cacheType */
        foreach ($cachePool as $cacheType) {
            $cacheType->getBackend()->clean();
        }
    }
}
