<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * The test covers \Magento\Framework\App\Cache_Type_* classes all at once, as all of them are similar
 */
namespace Magento\Framework\App\Test\Unit\Cache\Type;

class GenericTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $className
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($className)
    {
        $frontendMock = $this->createMock(\Magento\Framework\Cache\FrontendInterface::class);

        $poolMock = $this->createMock(\Magento\Framework\App\Cache\Type\FrontendPool::class);
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
            [\Magento\Framework\App\Cache\Type\Block::class],
            [\Magento\Framework\App\Cache\Type\Collection::class],
            [\Magento\Framework\App\Cache\Type\Config::class],
            [\Magento\Framework\App\Cache\Type\Layout::class],
            [\Magento\Framework\App\Cache\Type\Translate::class],
            [\Magento\Framework\App\Cache\Type\Block::class]
        ];
    }
}
