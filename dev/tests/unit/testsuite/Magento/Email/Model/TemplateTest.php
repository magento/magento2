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
namespace Magento\Email\Model;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTypeDataProvider
     * @param string $templateType
     * @param int $expectedResult
     */
    public function testGetType($templateType, $expectedResult)
    {
        $emailConfig = $this->getMockBuilder(
            '\Magento\Email\Model\Template\Config'
        )->setMethods(
            array('getTemplateType')
        )->disableOriginalConstructor()->getMock();
        $emailConfig->expects($this->once())->method('getTemplateType')->will($this->returnValue($templateType));
        /** @var \Magento\Email\Model\Template $model */
        $model = $this->getMockBuilder(
            'Magento\Email\Model\Template'
        )->setMethods(
            array('_init')
        )->setConstructorArgs(
            array(
                $this->getMock('Magento\Framework\Model\Context', array(), array(), '', false),
                $this->getMock('Magento\Core\Model\View\Design', array(), array(), '', false),
                $this->getMock('Magento\Framework\Registry', array(), array(), '', false),
                $this->getMock('Magento\Core\Model\App\Emulation', array(), array(), '', false),
                $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false),
                $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false),
                $this->getMock('Magento\Framework\View\Asset\Repository', array(), array(), '', false),
                $this->getMock('Magento\Framework\View\FileSystem', array(), array(), '', false),
                $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface'),
                $this->getMock('Magento\Email\Model\Template\FilterFactory', array(), array(), '', false),
                $emailConfig,
                array('template_id' => 10)
            )
        )->getMock();
        $this->assertEquals($expectedResult, $model->getType());
    }

    public function getTypeDataProvider()
    {
        return array(array('text', 1), array('html', 2));
    }
}
