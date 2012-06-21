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
 * @category    Varien
 * @package     Varien_Image
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Varien_Object test case.
 */
class Varien_Image_Adapter_ImageMagickTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Varien_Image_Adapter_ImageMagick
     */
    protected $_object;

    public function tearDown()
    {
        Magento_Test_Environment::getInstance()->cleanTmpDirOnShutdown();
    }

    /**
     * @dataProvider watermarkDataProvider
     */
    public function testWatermark($imagePath, $expectedResult)
    {
        try {
            $this->_object = new Varien_Image_Adapter_ImageMagick;
            $this->_object->watermark($imagePath);
            $this->fail('An expected exception has not been raised.');
        } catch (Exception $e) {
            $this->assertContains($e->getMessage(), $expectedResult);
        }
    }

    public function watermarkDataProvider()
    {
        $_tmpPath = Magento_Test_Environment::getInstance()->getTmpDir();
        $imageAbsent = $_tmpPath . DIRECTORY_SEPARATOR . md5(time() + microtime(true)) . '2';
        $imageExists = $_tmpPath . DIRECTORY_SEPARATOR . md5(time() + microtime(true)) . '1';
        touch($imageExists);

        return array(
            array('', Varien_Image_Adapter_ImageMagick::ERROR_WATERMARK_IMAGE_ABSENT),
            array($imageAbsent, Varien_Image_Adapter_ImageMagick::ERROR_WATERMARK_IMAGE_ABSENT),
            array($imageExists, Varien_Image_Adapter_ImageMagick::ERROR_WRONG_IMAGE),
        );
    }
}

