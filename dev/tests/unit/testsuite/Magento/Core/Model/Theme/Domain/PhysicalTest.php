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
 * Test theme domain physical model
 */
namespace Magento\Core\Model\Theme\Domain;

class PhysicalTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateVirtualTheme()
    {
        $physicalTheme = $this->getMock('Magento\Core\Model\Theme', array('__wakeup'), array(), '', false, false);
        $physicalTheme->setData(array('parent_id' => 10, 'theme_title' => 'Test Theme'));

        $copyService = $this->getMock('Magento\Theme\Model\CopyService', array('copy'), array(), '', false, false);
        $copyService->expects($this->once())->method('copy')->will($this->returnValue($copyService));

        $virtualTheme = $this->getMock(
            'Magento\Core\Model\Theme',
            array('__wakeup', 'getThemeImage', 'createPreviewImageCopy', 'save'),
            array(),
            '',
            false,
            false
        );
        $virtualTheme->expects($this->once())->method('getThemeImage')->will($this->returnValue($virtualTheme));

        $virtualTheme->expects(
            $this->once()
        )->method(
            'createPreviewImageCopy'
        )->will(
            $this->returnValue($virtualTheme)
        );

        $virtualTheme->expects($this->once())->method('save')->will($this->returnValue($virtualTheme));

        $themeFactory = $this->getMock('Magento\Core\Model\ThemeFactory', array('create'), array(), '', false, false);
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($virtualTheme));

        $themeCollection = $this->getMock(
            'Magento\Core\Model\Resource\Theme\Collection',
            array('addTypeFilter', 'addAreaFilter', 'addFilter', 'count'),
            array(),
            '',
            false,
            false
        );

        $themeCollection->expects($this->any())->method('addTypeFilter')->will($this->returnValue($themeCollection));

        $themeCollection->expects($this->any())->method('addAreaFilter')->will($this->returnValue($themeCollection));

        $themeCollection->expects($this->any())->method('addFilter')->will($this->returnValue($themeCollection));

        $themeCollection->expects($this->once())->method('count')->will($this->returnValue(1));

        $domainModel = new \Magento\Core\Model\Theme\Domain\Physical(
            $this->getMock('Magento\Framework\View\Design\ThemeInterface', array(), array(), '', false, false),
            $themeFactory,
            $copyService,
            $themeCollection
        );
        $domainModel->createVirtualTheme($physicalTheme);
    }
}
