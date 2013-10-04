<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * The test covers \Magento\Core\Model\Cache_Type_* classes all at once, as all of them are similar
 */
namespace Magento\Core\Model\Cache\Type;

class GenericTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $className
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($className)
    {
        $frontendMock = $this->getMock('Magento\Cache\FrontendInterface');

        $poolMock = $this->getMock('Magento\Core\Model\Cache\Type\FrontendPool', array(), array(), '', false);
        $poolMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($className::TYPE_IDENTIFIER)
            ->will($this->returnValue($frontendMock));

        $model = new $className($poolMock);

        // Test initialization was done right
        $this->assertEquals($className::CACHE_TAG, $model->getTag(), 'The tag is wrong');

        // Test that frontend is now engaged in operations
        $frontendMock->expects($this->once())
            ->method('load')
            ->with(26);
        $model->load(26);
    }

    /**
     * @return array
     */
    public static function constructorDataProvider()
    {
        return array(
            array('Magento\Core\Model\Cache\Type\Block'),
            array('Magento\Core\Model\Cache\Type\Collection'),
            array('Magento\Core\Model\Cache\Type\Config'),
            array('Magento\Core\Model\Cache\Type\Layout'),
            array('Magento\Core\Model\Cache\Type\Translate'),
            array('Magento\Core\Model\Cache\Type\Block'),
        );
    }
}
