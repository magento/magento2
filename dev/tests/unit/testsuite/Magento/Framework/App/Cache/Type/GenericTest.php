<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * The test covers \Magento\Framework\App\Cache_Type_* classes all at once, as all of them are similar
 */
namespace Magento\Framework\App\Cache\Type;

class GenericTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $className
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($className)
    {
        $frontendMock = $this->getMock('Magento\Framework\Cache\FrontendInterface');

        $poolMock = $this->getMock('Magento\Framework\App\Cache\Type\FrontendPool', [], [], '', false);
        $poolMock->expects(
            $this->atLeastOnce()
        )->method(
            'get'
        )->with(
            $className::TYPE_IDENTIFIER
        )->will(
            $this->returnValue($frontendMock)
        );

        $model = new $className($poolMock);

        // Test initialization was done right
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
            ['Magento\Framework\App\Cache\Type\Block'],
            ['Magento\Framework\App\Cache\Type\Collection'],
            ['Magento\Framework\App\Cache\Type\Config'],
            ['Magento\Framework\App\Cache\Type\Layout'],
            ['Magento\Framework\App\Cache\Type\Translate'],
            ['Magento\Framework\App\Cache\Type\Block']
        ];
    }
}
