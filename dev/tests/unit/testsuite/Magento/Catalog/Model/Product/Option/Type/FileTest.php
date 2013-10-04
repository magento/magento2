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
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Product\Option\Type;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider createWritableDirDataProvider
     * @param boolean $isWritable
     * @param boolean $throwException
     */
    public function testCreateWritableDir($isWritable, $throwException)
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $filesystemMock = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $filesystemMock->expects($this->once())
            ->method('isWritable')
            ->will($this->returnValue($isWritable));
        if (!$isWritable) {
            $filesystemMock->expects($this->once())
                ->method('createDirectory')
                ->will($throwException
                    ? $this->throwException(new \Magento\Core\Exception)
                    : $this->returnValue(null)
                );
        } else {
            $filesystemMock->expects($this->never())->method('createDirectory');
        }

        if ($throwException) {
            $this->setExpectedException('Magento\Core\Exception');
        }

        $optionFactoryMock = $this->getMock('Magento\Sales\Model\Quote\Item\OptionFactory', array(), array(),
            '', false);
        $model = $helper->getObject('Magento\Catalog\Model\Product\Option\Type\File', array(
            'filesystem' => $filesystemMock,
            'itemOptionFactory' => $optionFactoryMock,
        ));
        $method = new \ReflectionMethod('Magento\Catalog\Model\Product\Option\Type\File', '_createWritableDir');
        $method->setAccessible(true);
        $method->invoke($model, 'dummy/path');
    }

    /**
     * @see self::testCreateWritableDir()
     * @return array
     */
    public function createWritableDirDataProvider()
    {
        return array(
            array(true, false),
            array(false, false),
            array(false, true),
        );
    }
}
