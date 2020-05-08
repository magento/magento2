<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Adapter\Index\Builder;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
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

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            Builder::class,
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
