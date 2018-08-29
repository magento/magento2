<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Adapter\Index\Builder;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Builder
     */
    protected $model;

    /**
     * @var LocaleResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolver;

    /**
     * @var EsConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $esConfig;

    /**
     * Setup method
     * @return void
     */
    protected function setUp()
    {
        $this->localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'emulate',
                'getLocale'
            ])
            ->getMock();
        $this->esConfig = $this->getMockBuilder(
            \Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Index\Builder::class,
            [
                'localeResolver' => $this->localeResolver,
                'esConfig' => $this->esConfig
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
