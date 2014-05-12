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
 * Test theme staging model
 */
namespace Magento\Core\Model\Theme\Domain;

class StagingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Core\Model\Theme\Domain\Staging::updateFromStagingTheme
     */
    public function testUpdateFromStagingTheme()
    {
        $parentTheme = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false, false);

        $theme = $this->getMock(
            'Magento\Core\Model\Theme',
            array('__wakeup', 'getParentTheme'),
            array(),
            '',
            false,
            false
        );
        $theme->expects($this->once())->method('getParentTheme')->will($this->returnValue($parentTheme));

        $themeCopyService = $this->getMock('Magento\Theme\Model\CopyService', array('copy'), array(), '', false);
        $themeCopyService->expects($this->once())->method('copy')->with($theme, $parentTheme);

        $object = new \Magento\Core\Model\Theme\Domain\Staging($theme, $themeCopyService);
        $this->assertSame($object, $object->updateFromStagingTheme());
    }
}
