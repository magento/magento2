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
namespace Magento\Framework\App;

class AreaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Area
     */
    protected $_model;

    public static function tearDownAfterClass()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\CacheInterface')
            ->clean(array(\Magento\Core\Model\Design::CACHE_TAG));
    }

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        /** @var $_model \Magento\Framework\App\Area */
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Area',
            array('areaCode' => 'frontend')
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testInitDesign()
    {
        $defaultTheme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setDefaultDesignTheme()->getDesignTheme();
        $this->_model->load(\Magento\Framework\App\Area::PART_DESIGN);
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setDefaultDesignTheme();

        $this->assertEquals($defaultTheme->getThemePath(), $design->getDesignTheme()->getThemePath());
        $this->assertEquals('frontend', $design->getArea());

        // try second time and make sure it won't load second time
        $this->_model->load(\Magento\Framework\App\Area::PART_DESIGN);
        $designArea = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->getArea();
        $sameDesign = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setArea(
            $designArea
        );
        $this->assertSame($design, $sameDesign);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoConfigFixture current_store design/theme/ua_regexp a:1:{s:1:"_";a:2:{s:6:"regexp";s:10:"/firefox/i";s:5:"value";s:13:"Magento/blank";}}
     * @magentoConfigFixture current_store design/package/ua_regexp a:1:{s:1:"_";a:2:{s:6:"regexp";s:10:"/firefox/i";s:5:"value";s:13:"Magento/blank";}}
     * @magentoAppIsolation enabled
     */
    // @codingStandardsIgnoreEnd
    public function testDetectDesignUserAgent()
    {

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->create('Magento\TestFramework\Request');
        $request->setServer(array('HTTP_USER_AGENT' => 'Mozilla Firefox'));
        $this->_model->detectDesign($request);
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        );
        $this->assertEquals('Magento/blank', $design->getDesignTheme()->getThemePath());
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoConfigFixture current_store design/theme/ua_regexp a:1:{s:1:"_";a:2:{s:6:"regexp";s:10:"/firefox/i";s:5:"value";s:13:"Magento/blank";}}
     * @magentoConfigFixture current_store design/package/ua_regexp a:1:{s:1:"_";a:2:{s:6:"regexp";s:10:"/firefox/i";s:5:"value";s:13:"Magento/blank";}}
     * @magentoDataFixture Magento/Core/_files/design_change.php
     * @magentoAppIsolation enabled
     */
    // @codingStandardsIgnoreEnd
    public function testDetectDesignDesignChange()
    {
        $this->_model->detectDesign();
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        );
        $this->assertEquals('Magento/plushe', $design->getDesignTheme()->getThemePath());
    }

    // @codingStandardsIgnoreStart
    /**
     * Test that non-frontend areas are not affected neither by user-agent reg expressions, nor by the "design change"
     *
     * @magentoConfigFixture current_store design/theme/ua_regexp a:1:{s:1:"_";a:2:{s:6:"regexp";s:10:"/firefox/i";s:5:"value";s:13:"Magento/blank";}}
     * magentoDataFixture Magento/Core/_files/design_change.php
     * @magentoAppIsolation enabled
     */
    // @codingStandardsIgnoreEnd
    public function testDetectDesignNonFrontend()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $model = $objectManager->create('Magento\Framework\App\Area', array('areaCode' => 'install'));
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->create('Magento\TestFramework\Request');
        $request->setServer(array('HTTP_USER_AGENT' => 'Mozilla Firefox'));
        $model->detectDesign($request);
        $design = $objectManager->get('Magento\Framework\View\DesignInterface');
        $this->assertNotEquals('Magento/blank', $design->getDesignTheme()->getThemePath());
    }
}
