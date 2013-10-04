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
 * @package     Magento_Install
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Install\Block\Begin
 */
namespace Magento\Install\Block;

class BeginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get block model
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|\Magento\Filesystem $contextFileSystem
     * @param string|null $fileName
     * @return \Magento\Install\Block\Begin
     */
    protected function _getBlockModel($contextFileSystem, $fileName = null)
    {
        $helper = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $context = $this->getMock('Magento\Core\Block\Template\Context', array(), array(), '', false);
        $context->expects($this->once())->method('getFileSystem')->will($this->returnValue($contextFileSystem));
        $installer = $this->getMock('Magento\Install\Model\Installer', array(), array(), '', false);
        $wizard = $this->getMock('Magento\Install\Model\Wizard', array(), array(), '', false);
        $session = $this->getMock('Magento\Core\Model\Session\Generic', array(), array(), '', false);
        $block = new \Magento\Install\Block\Begin($helper, $context, $installer, $wizard, $session, $fileName, array());
        return $block;
    }

    /**
     * @dataProvider getLicenseHtmlWhenFileExistsDataProvider
     *
     * @param $fileName
     * @param $expectedTxt
     */
    public function testGetLicenseHtmlWhenFileExists($fileName, $expectedTxt)
    {
        $fileSystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $fileSystem->expects($this->once())
            ->method('read')
            ->with($this->equalTo(BP . DS . $fileName))
            ->will($this->returnValue($expectedTxt));

        $block = $this->_getBlockModel($fileSystem, $fileName);
        $this->assertEquals($expectedTxt, $block->getLicenseHtml());
    }

    /**
     * Test for getLicenseHtml when EULA file name is empty
     *
     * @dataProvider getLicenseHtmlWhenFileIsEmptyDataProvider
     *
     * @param $fileName
     */
    public function testGetLicenseHtmlWhenFileIsEmpty($fileName)
    {
        $fileSystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $fileSystem->expects($this->never())->method('read');

        $block = $this->_getBlockModel($fileSystem, $fileName);
        $this->assertEquals('', $block->getLicenseHtml());
    }

    /**
     * Data provider for testGetLicenseHtmlWhenFileExists
     *
     * @return array
     */
    public function getLicenseHtmlWhenFileExistsDataProvider()
    {
        return array(
            'Lycense for EE' => array(
                'LICENSE_TEST1.html',
                'HTML for EE LICENSE'
            ),
            'Lycense for CE' => array(
                'LICENSE_TEST2.html',
                'HTML for CE LICENSE'
            ),
            'empty file' => array(
                'LICENSE_TEST3.html',
                ''
            )
        );
    }

    /**
     * Data provider for testGetLicenseHtmlWhenFileIsEmpty
     *
     * @return array
     */
    public function getLicenseHtmlWhenFileIsEmptyDataProvider()
    {
        return array(
            'no filename' => array(null),
            'empty filename' => array('')
        );
    }
}
