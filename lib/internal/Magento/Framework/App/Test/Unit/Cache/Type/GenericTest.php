<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * The test covers \Magento\Framework\App\Cache_Type_* classes all at once, as all of them are similar
 */
namespace Magento\Framework\App\Test\Unit\Cache\Type;

use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\Cache\Type\Collection;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\App\Cache\Type\Layout;
use Magento\Framework\App\Cache\Type\Translate;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    /**
     * @param string $className
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($className)
    {
        $frontendMock = $this->getMockForAbstractClass(FrontendInterface::class);

        $poolMock = $this->createMock(FrontendPool::class);
        /** @noinspection PhpUndefinedFieldInspection */
        $poolMock->expects(
            $this->atLeastOnce()
        )->method(
            'get'
        )->with(
            $className::TYPE_IDENTIFIER
        )->willReturn(
            $frontendMock
        );

        $model = new $className($poolMock);

        // Test initialization was done right
        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertEquals($className::CACHE_TAG, $model->getTag(), 'The tag is wrong');

        // Test that frontend is now engaged in operations
        $frontendMock->expects($this->once())->method('load')->with(26);
        $model->load(26);
    }

    /**
     * @return array
     */
    public static function constructorDataProvider()
    {
        return [
            [Block::class],
            [Collection::class],
            [Config::class],
            [Layout::class],
            [Translate::class],
            [Block::class]
        ];
    }
}
