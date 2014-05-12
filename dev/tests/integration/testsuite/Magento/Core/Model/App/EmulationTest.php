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
namespace Magento\Core\Model\App;

class EmulationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\App\Emulation
     */
    protected $_model;

    /**
     * @covers \Magento\Core\Model\App\Emulation::startEnvironmentEmulation
     * @covers \Magento\Core\Model\App\Emulation::stopEnvironmentEmulation
     */
    public function testEnvironmentEmulation()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\App\Emulation');
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\View\DesignInterface');

        $initialEnvInfo = $this->_model->startEnvironmentEmulation(1);
        $initialDesign = $initialEnvInfo->getInitialDesign();
        $this->assertEquals(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE, $initialDesign['area']);
        $this->assertEquals(\Magento\Framework\App\Area::AREA_FRONTEND, $design->getDesignTheme()->getData('area'));

        $this->_model->stopEnvironmentEmulation($initialEnvInfo);
        $this->assertEquals(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE, $design->getArea());
    }
}
