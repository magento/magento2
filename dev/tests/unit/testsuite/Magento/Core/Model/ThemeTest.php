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

/**
 * Test theme model
 */
namespace Magento\Core\Model;

use Magento\Framework\View\Design\ThemeInterface;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Theme|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_imageFactory;

    protected function setUp()
    {
        $customizationConfig = $this->getMock('Magento\Theme\Model\Config\Customization', array(), array(), '', false);
        $customizationFactory = $this->getMock(
            'Magento\Framework\View\Design\Theme\CustomizationFactory',
            array('create'),
            array(),
            '',
            false
        );
        $resourceCollection = $this->getMock(
            'Magento\Core\Model\Resource\Theme\Collection',
            array(),
            array(),
            '',
            false
        );
        $this->_imageFactory = $this->getMock(
            'Magento\Framework\View\Design\Theme\ImageFactory',
            array('create'),
            array(),
            '',
            false
        );

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            'Magento\Core\Model\Theme',
            array(
                'customizationFactory' => $customizationFactory,
                'customizationConfig' => $customizationConfig,
                'imageFactory' => $this->_imageFactory,
                'resourceCollection' => $resourceCollection
            )
        );

        $this->_model = $objectManagerHelper->getObject('Magento\Core\Model\Theme', $arguments);
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @covers \Magento\Core\Model\Theme::getThemeImage
     */
    public function testThemeImageGetter()
    {
        $this->_imageFactory->expects($this->once())->method('create')->with(array('theme' => $this->_model));
        $this->_model->getThemeImage();
    }

    /**
     * @dataProvider isVirtualDataProvider
     * @param int $type
     * @param string $isVirtual
     * @covers \Magento\Core\Model\Theme::isVirtual
     */
    public function testIsVirtual($type, $isVirtual)
    {
        /** @var $themeModel \Magento\Core\Model\Theme */
        $themeModel = $this->getMock('Magento\Core\Model\Theme', array('__wakeup'), array(), '', false);
        $themeModel->setType($type);
        $this->assertEquals($isVirtual, $themeModel->isVirtual());
    }

    /**
     * @return array
     */
    public function isVirtualDataProvider()
    {
        return array(
            array('type' => ThemeInterface::TYPE_VIRTUAL, 'isVirtual' => true),
            array('type' => ThemeInterface::TYPE_STAGING, 'isVirtual' => false),
            array('type' => ThemeInterface::TYPE_PHYSICAL, 'isVirtual' => false)
        );
    }

    /**
     * @dataProvider isPhysicalDataProvider
     * @param int $type
     * @param string $isPhysical
     * @covers \Magento\Core\Model\Theme::isPhysical
     */
    public function testIsPhysical($type, $isPhysical)
    {
        /** @var $themeModel \Magento\Core\Model\Theme */
        $themeModel = $this->getMock('Magento\Core\Model\Theme', array('__wakeup'), array(), '', false);
        $themeModel->setType($type);
        $this->assertEquals($isPhysical, $themeModel->isPhysical());
    }

    /**
     * @return array
     */
    public function isPhysicalDataProvider()
    {
        return array(
            array('type' => ThemeInterface::TYPE_VIRTUAL, 'isPhysical' => false),
            array('type' => ThemeInterface::TYPE_STAGING, 'isPhysical' => false),
            array('type' => ThemeInterface::TYPE_PHYSICAL, 'isPhysical' => true)
        );
    }

    /**
     * @dataProvider isVisibleDataProvider
     * @param int $type
     * @param string $isVisible
     * @covers \Magento\Core\Model\Theme::isVisible
     */
    public function testIsVisible($type, $isVisible)
    {
        /** @var $themeModel \Magento\Core\Model\Theme */
        $themeModel = $this->getMock('Magento\Core\Model\Theme', array('__wakeup'), array(), '', false);
        $themeModel->setType($type);
        $this->assertEquals($isVisible, $themeModel->isVisible());
    }

    /**
     * @return array
     */
    public function isVisibleDataProvider()
    {
        return array(
            array('type' => ThemeInterface::TYPE_VIRTUAL, 'isVisible' => true),
            array('type' => ThemeInterface::TYPE_STAGING, 'isVisible' => false),
            array('type' => ThemeInterface::TYPE_PHYSICAL, 'isVisible' => true)
        );
    }

    /**
     * Test id deletable
     *
     * @dataProvider isDeletableDataProvider
     * @param string $themeType
     * @param bool $isDeletable
     * @covers \Magento\Core\Model\Theme::isDeletable
     */
    public function testIsDeletable($themeType, $isDeletable)
    {
        /** @var $themeModel \Magento\Core\Model\Theme */
        $themeModel = $this->getMock('Magento\Core\Model\Theme', array('getType', '__wakeup'), array(), '', false);
        $themeModel->expects($this->once())->method('getType')->will($this->returnValue($themeType));
        $this->assertEquals($isDeletable, $themeModel->isDeletable());
    }

    /**
     * @return array
     */
    public function isDeletableDataProvider()
    {
        return array(
            array(ThemeInterface::TYPE_VIRTUAL, true),
            array(ThemeInterface::TYPE_STAGING, true),
            array(ThemeInterface::TYPE_PHYSICAL, false)
        );
    }

    /**
     * @param mixed $originalCode
     * @param string $expectedCode
     * @dataProvider getCodeDataProvider
     */
    public function testGetCode($originalCode, $expectedCode)
    {
        $this->_model->setCode($originalCode);
        $this->assertSame($expectedCode, $this->_model->getCode());
    }

    /**
     * @return array
     */
    public function getCodeDataProvider()
    {
        return array(
            'string code' => array('theme/code', 'theme/code'),
            'null code' => array(null, ''),
            'number code' => array(10, '10')
        );
    }
}
