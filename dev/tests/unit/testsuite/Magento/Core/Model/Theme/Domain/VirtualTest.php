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

/**
 * Test theme virtual model
 */
namespace Magento\Core\Model\Theme\Domain;

class VirtualTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get existing staging theme
     *
     * @covers \Magento\Core\Model\Theme\Domain\Virtual::getStagingTheme
     */
    public function testGetStagingThemeExisting()
    {
        $themeStaging = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false, false);

        $theme = $this->getMock('Magento\Core\Model\Theme', array('getStagingVersion'), array(), '', false, false);
        $theme->expects($this->once())->method('getStagingVersion')->will($this->returnValue($themeStaging));

        $themeFactory = $this->getMock('Magento\Core\Model\ThemeFactory', array('create'), array(), '', false);
        $themeFactory->expects($this->never())->method('create');

        $themeCopyService = $this->getMock('Magento\Core\Model\Theme\CopyService', array('copy'), array(), '', false);
        $themeCopyService->expects($this->never())->method('copy');

        $customizationConfig = $this->getMock('Magento\Theme\Model\Config\Customization', array(), array(), '', false);

        $object = new \Magento\Core\Model\Theme\Domain\Virtual(
            $theme, $themeFactory, $themeCopyService, $customizationConfig
        );

        $this->assertSame($themeStaging, $object->getStagingTheme());
        $this->assertSame($themeStaging, $object->getStagingTheme());
    }

    /**
     * Test creating staging theme
     *
     * @covers \Magento\Core\Model\Theme\Domain\Virtual::getStagingTheme
     */
    public function testGetStagingThemeNew()
    {
        $theme = $this->getMock('Magento\Core\Model\Theme', array('getStagingVersion'), array(), '', false, false);
        $theme->expects($this->once())->method('getStagingVersion')->will($this->returnValue(null));
        /** @var $theme \Magento\Object */
        $theme->setData(array(
            'id'                    => 'fixture_theme_id',
            'theme_version'         => 'fixture_theme_version',
            'theme_title'           => 'fixture_theme_title',
            'preview_image'         => 'fixture_preview_image',
            'is_featured'           => 'fixture_is_featured',
            'area'                  => 'fixture_area',
            'type'                  => \Magento\Core\Model\Theme::TYPE_VIRTUAL
        ));

        $themeStaging = $this->getMock('Magento\Core\Model\Theme', array('setData', 'save'), array(), '', false, false);
        $themeStaging->expects($this->at(0))->method('setData')->with(array(
            'parent_id'             => 'fixture_theme_id',
            'theme_path'            => null,
            'theme_version'         => 'fixture_theme_version',
            'theme_title'           => 'fixture_theme_title - Staging',
            'preview_image'         => 'fixture_preview_image',
            'is_featured'           => 'fixture_is_featured',
            'area'                  => 'fixture_area',
            'type'                  => \Magento\Core\Model\Theme::TYPE_STAGING,
        ));
        $themeStaging->expects($this->at(1))->method('save');

        $themeFactory = $this->getMock('Magento\Core\Model\ThemeFactory', array('create'), array(), '', false);
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($themeStaging));

        $themeCopyService = $this->getMock('Magento\Core\Model\Theme\CopyService', array('copy'), array(), '', false);
        $themeCopyService->expects($this->once())->method('copy')->with($theme, $themeStaging);

        $customizationConfig = $this->getMock('Magento\Theme\Model\Config\Customization', array(), array(), '', false);

        $object = new \Magento\Core\Model\Theme\Domain\Virtual(
            $theme, $themeFactory, $themeCopyService, $customizationConfig
        );

        $this->assertSame($themeStaging, $object->getStagingTheme());
        $this->assertSame($themeStaging, $object->getStagingTheme());
    }

    /**
     * Test for is assigned method
     *
     * @covers \Magento\Core\Model\Theme\Domain\Virtual::isAssigned
     */
    public function testIsAssigned()
    {
        $customizationConfig = $this->getMock(
            'Magento\Theme\Model\Config\Customization', array('isThemeAssignedToStore'), array(), '', false
        );
        $themeMock = $this->getMock('Magento\Core\Model\Theme', array('getCollection', 'getId'), array(), '', false,
            false);
        $customizationConfig->expects($this->atLeastOnce())->method('isThemeAssignedToStore')
            ->with($themeMock)
            ->will($this->returnValue(true));
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments('Magento\Core\Model\Theme\Domain\Virtual',
            array(
                 'theme' => $themeMock,
                 'customizationConfig' => $customizationConfig,
            )
        );
        /** @var $model \Magento\Core\Model\Theme\Domain\Virtual */
        $model = $objectManagerHelper->getObject('Magento\Core\Model\Theme\Domain\Virtual', $constructArguments);
        $this->assertEquals(true, $model->isAssigned());
    }
}
