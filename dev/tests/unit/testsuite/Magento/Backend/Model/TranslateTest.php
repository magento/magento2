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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Model;

class TranslateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider initDataProvider
     *
     * @param string $area
     * @param string $expectedScope
     */
    public function testInit($area, $expectedScope)
    {
        $localeMock = $this->getMock('\Magento\Core\Model\LocaleInterface');
        $appMock = $this->getMock('\Magento\AppInterface', array(), array(), '', false);
        $appMock->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue($localeMock));
        $appStateMock = $this->getMock('\Magento\App\State', array(), array(), '', false);
        $appStateMock->expects($this->any())
            ->method('getAreaCode')
            ->will($this->returnValue($area));
        $scopeMock = $this->getMock('\Magento\BaseScopeInterface');
        $scopeResolverMock = $this->getMock('\Magento\BaseScopeResolverInterface');
        $scopeResolverMock->expects($this->once())
            ->method('getScope')
            ->will($this->returnValue($scopeMock));
        $themeMock = $this->getMock('\Magento\View\Design\ThemeInterface', array());
        $themeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $designMock = $this->getMock('\Magento\View\DesignInterface');
        $designMock->expects($this->once())
            ->method('getDesignTheme')
            ->will($this->returnValue($themeMock));

        $inlineMock = $this->getMock('\Magento\Translate\InlineInterface');
        $inlineMock->expects($this->at(0))
            ->method('isAllowed')
            ->with()
            ->will($this->returnValue(false));
        $inlineMock->expects($this->at(1))
            ->method('isAllowed')
            ->with($this->equalTo($expectedScope))
            ->will($this->returnValue(true));
        $translateFactoryMock = $this->getMock('\Magento\Translate\Factory', array(), array(), '', false);
        $translateFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($inlineMock));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Backend\Model\Translate $translate */
        $translate = $helper->getObject('Magento\Backend\Model\Translate', array(
            'app' => $appMock,
            'appState' => $appStateMock,
            'scopeResolver' => $scopeResolverMock,
            'viewDesign' => $designMock,
            'translateFactory' => $translateFactoryMock
        ));
        $translate->init();
    }

    public function initDataProvider()
    {
        return array(
            array('adminhtml', 'admin'),
            array('frontend', null),
        );
    }
}
