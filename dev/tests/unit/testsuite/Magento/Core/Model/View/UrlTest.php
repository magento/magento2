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
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\View;

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
        /** @var $filesystem \Magento\Filesystem|PHPUnit_Framework_MockObject_MockObject */
        $filesystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);
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
        /** @var $dirs \Magento\App\Dir|PHPUnit_Framework_MockObject_MockObject */
        $dirs = $this->getMock('Magento\App\Dir', array(), array(), '', false);
        $dirs->expects($this->any())
            ->method('getDir')
            ->will($this->returnValue('some_dir'));

        // 3. Get store model
        $store = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $store->expects($this->any())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://example.com/'));
        $store->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($isSigned));

        // 4. Get store manager
        /** @var $storeManager \Magento\Core\Model\StoreManager|PHPUnit_Framework_MockObject_MockObject */
        $storeManager = $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false);
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        // 5. Get viewService model
        /** @var $viewService \Magento\Core\Model\View\Service|PHPUnit_Framework_MockObject_MockObject */
        $viewService = $this->getMock('Magento\Core\Model\View\Service',
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
        /** @var $publisher \Magento\Core\Model\View\Publisher|PHPUnit_Framework_MockObject_MockObject */
        $publisher = $this->getMock('Magento\Core\Model\View\Publisher', array(), array(), '', false);
        $publisher->expects($this->any())
            ->method('getPublicFilePath')
            ->will($this->returnValue(str_replace('/', DIRECTORY_SEPARATOR, 'some_dir/public_dir/a/t/m/file.js')));

        // 7. Get deployed file manager
        /** @var $dFManager \Magento\Core\Model\View\DeployedFilesManager|PHPUnit_Framework_MockObject_MockObject */
        $dFManager = $this->getMock('Magento\Core\Model\View\DeployedFilesManager', array(), array(), '',
            false
        );

        // Create model to be tested
        /** @var $model \Magento\Core\Model\View\Url|PHPUnit_Framework_MockObject_MockObject */
        $model = new \Magento\Core\Model\View\Url(
            $filesystem, $dirs, $storeManager, $viewService, $publisher, $dFManager
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
