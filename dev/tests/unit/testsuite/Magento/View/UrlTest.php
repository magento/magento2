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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param \Magento\View\Design\ThemeInterface $themeModel
     * @dataProvider getViewFileUrlProductionModeDataProvider
     */
    public function testGetViewFileUrlProductionMode($themeModel)
    {
        $isProductionMode = true;
        $isSigned = false;      //NOTE: If going to test with signature enabled mock \Magento\Filesystem::getMTime()
        $expected = 'http://example.com/public_dir/a/t/m/file.js';

        // 1. Get fileSystem model
        /** @var $filesystem \Magento\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
        $filesystem = $this->getMock('Magento\App\Filesystem', array(), array(), '', false);
        $filesystem->expects($this->never())
            ->method('isFile');
        $filesystem->expects($this->never())
            ->method('isDirectory');
        $filesystem->expects($this->never())
            ->method('read');
        $filesystem->expects($this->never())
            ->method('write');
        $filesystem->expects($this->never())
            ->method('copy');

        // 2. Get directories configuration
        $filesystem->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('some_dir'));

        // 3. Get url model
        $urlBuilder = $this->getMockBuilder('Magento\UrlInterface')->getMockForAbstractClass();
        $urlBuilder->expects($this->any())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://example.com/'));

        // 4. Get urlConfig model
        $urlConfig = $this->getMockBuilder('Magento\View\Url\ConfigInterface')->getMockForAbstractClass();
        $urlConfig->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($isSigned));

        // 5. Get viewService model
        /** @var $viewService \Magento\View\Service|\PHPUnit_Framework_MockObject_MockObject */
        $viewService = $this->getMock('Magento\View\Service',
            array('updateDesignParams', 'extractScope', 'isViewFileOperationAllowed'), array(), '', false
        );
        $viewService->expects($this->any())
            ->method('extractScope')
            ->will($this->returnArgument(0));
        $viewService->expects($this->any())
            ->method('isViewFileOperationAllowed')
            ->will($this->returnValue($isProductionMode));
        $viewService->expects($this->any())
            ->method('updateDesignParams');

        // 6. Get publisher model
        /** @var $publisher \Magento\View\Publisher|\PHPUnit_Framework_MockObject_MockObject */
        $publisher = $this->getMock('Magento\View\Publisher', array(), array(), '', false);
        $publisher->expects($this->any())
            ->method('getPublicFilePath')
            ->will($this->returnValue('some_dir/public_dir/a/t/m/file.js'));

        // 7. Get deployed file manager
        /** @var $dFManager \Magento\View\DeployedFilesManager|\PHPUnit_Framework_MockObject_MockObject */
        $dFManager = $this->getMock('Magento\View\DeployedFilesManager', array(), array(), '',
            false
        );
        $viewFilesystem = $this->getMock('Magento\View\Filesystem', array(), array(), '', false);

        // 8. Get default fake url map
        $urlMap = array('fake' => array('key' => "some_key", 'value' => "some_value"));

        // Create model to be tested
        /** @var $model \Magento\View\Url|\PHPUnit_Framework_MockObject_MockObject */
        $model = new \Magento\View\Url(
            $filesystem, $urlBuilder, $urlConfig, $viewService, $publisher, $dFManager, $viewFilesystem, $urlMap
        );

        // Test
        $actual = $model->getViewFileUrl('file.js', array(
            'area'       => 'a',
            'themeModel' => $themeModel,
            'locale'     => 'l',
            'module'     => 'm'
        ));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function getViewFileUrlProductionModeDataProvider()
    {
        $usualTheme = \PHPUnit_Framework_MockObject_Generator::getMock(
            'Magento\View\Design\ThemeInterface',
            array(),
            array(),
            '',
            false,
            false
        );
        $virtualTheme = clone $usualTheme;
        $parentOfVirtualTheme = clone $usualTheme;

        $usualTheme->expects(new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(1))
            ->method('getThemePath')
            ->will(new \PHPUnit_Framework_MockObject_Stub_Return('t'));

        $parentOfVirtualTheme->expects(new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(1))
            ->method('getThemePath')
            ->will(new \PHPUnit_Framework_MockObject_Stub_Return('t'));

        $virtualTheme->expects(new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(1))
            ->method('getParentTheme')
            ->will(new \PHPUnit_Framework_MockObject_Stub_Return($parentOfVirtualTheme));

        return array(
            'usual theme' => array(
                $usualTheme
            ),
            'virtual theme' => array(
                $virtualTheme
            ),
        );
    }
}
