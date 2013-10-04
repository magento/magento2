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
 * @package     Magento_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested block
     *
     * @var \Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons\Save
     */
    protected $_block;

    /**
     * @var string
     */
    protected $_url = 'http://some.url.com';

    protected function setUp()
    {
        // 1. Get helper mock
        /** @var $helper \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock('Magento\Backend\Helper\Data', array('escapeHtml'), array(), '', false);
        $helper->expects($this->any())
            ->method('escapeHtml')
            ->will($this->returnArgument(0));

        // 2. Inject helper to helper factory
        /** @var $helperFactory \Magento\Core\Model\Factory\Helper|\PHPUnit_Framework_MockObject_MockObject */
        $helperFactory = $this->getMock('Magento\Core\Model\Factory\Helper', array('get'), array(), '', false);
        $helperFactory->expects($this->any())
            ->method('get')
            ->with('Magento\Backend\Helper\Data')
            ->will($this->returnValue($helper));

        // 3. Get service mock
        /** @var $service \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
        $service = $this->getMock('Magento\Backend\Helper\Data', array('escapeHtml'), array(), '', false);

        // 4. Get URL model
        /** @var $urlBuilder \Magento\Core\Model\Url|\PHPUnit_Framework_MockObject_MockObject */
        $urlBuilder = $this->getMock('Magento\Core\Model\Url', array('getUrl'), array(), '', false);
        $urlBuilder->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($this->_url));

        //5. Run functionality
        $testHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $data = array(
            'helperFactory' => $helperFactory,
            'service'       => $service,
            'urlBuilder'    => $urlBuilder
        );
        $this->_block = $testHelper->getObject('Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons\Save',
            $data);
    }

    /**
     * @param \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject $theme
     * @param string $expected
     * @param array $expectedOptions
     * @dataProvider initDataProvider
     */
    public function testInit($theme, $expected, $expectedOptions)
    {
        $block = $this->_block;

        $block->setTheme($theme);
        $block->init();
        $data = $block->getData();
        $options = $block->getOptions();
        $mainAction = json_decode($data['data_attribute']['mage-init'], true);

        if ($mainAction['button']['eventData']['confirm'] !== false) {
            $this->assertNotEmpty($mainAction['button']['eventData']['confirm']['message']);
            $this->assertNotEmpty($mainAction['button']['eventData']['confirm']['title']);
        }

        $mainAction['button']['eventData']['confirm'] = array();

        $this->assertEquals($expected, $mainAction);
        foreach ($options as $option) {
            $action = json_decode($option['data_attribute']['mage-init'], true);

            $this->assertNotEmpty($action['button']['eventData']['confirm']['message']);
            $this->assertNotEmpty($action['button']['eventData']['confirm']['title']);
            $action['button']['eventData']['confirm'] = array();

            $isFound = false;
            foreach ($expectedOptions as $expectedOption) {
                try {
                    $this->assertEquals($expectedOption, $action);
                    $isFound = true;
                } catch (\Exception $e) {
                    //do nothing
                }
            }

            if (!$isFound) {
                $this->fail(sprintf('Option [%s] is not found', $option['data_attribute']['mage-init']));
            }
        }
    }

    /**
     * @return array
     */
    public function initDataProvider()
    {
        return array(
            'Physical theme' => array(
                $this->_getThemeMock(\Magento\Core\Model\Theme::TYPE_PHYSICAL),
                array(
                    'button' => array(
                        'event'     => 'assign',
                        'target'    => 'body',
                        'eventData' => array(
                            'theme_id'        => 123,
                            'confirm' => array()
                        )
                    )
                ),
                array()
            ),
            'Virtual assigned theme' => array(
                $this->_getThemeMock(\Magento\Core\Model\Theme::TYPE_VIRTUAL, true),
                array(
                    'button' => array(
                        'event'     => 'save',
                        'target'    => 'body',
                        'eventData' => array(
                            'theme_id'        => 123,
                            'save_url'        => $this->_url,
                            'confirm' => array()
                        )
                    )
                ),
                array()
            ),
            'Virtual unassigned theme' => array(
                $this->_getThemeMock(\Magento\Core\Model\Theme::TYPE_VIRTUAL, false),
                array(
                    'button' => array(
                        'event'     => 'save',
                        'target'    => 'body',
                        'eventData' => array(
                            'theme_id'        => 123,
                            'save_url'        => $this->_url,
                            'confirm' => array()
                        )
                    ),
                ),
                array(
                    array(
                        'button' => array(
                            'event'     => 'save',
                            'target'    => 'body',
                            'eventData' => array(
                                'theme_id' => 123,
                                'save_url' => $this->_url,
                                'confirm' => array()
                            )
                        ),
                    ),
                    array(
                        'button' => array(
                            'event'     => 'save-and-assign',
                            'target'    => 'body',
                            'eventData' => array(
                                'theme_id' => 123,
                                'save_url' => $this->_url,
                                'confirm' => array()
                            )
                        ),
                    )
                )
            )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid theme of a "2" type passed to save button block
     */
    public function testInitStaging()
    {
        // 1. Get theme mock
        $stagingTheme = $this->_getThemeMock(\Magento\Core\Model\Theme::TYPE_STAGING);

        $block = $this->_block;

        $block->setTheme($stagingTheme);
        $block->init();
    }

    /**
     * @param int $type
     * @param null|bool $isAssigned
     * @return \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getThemeMock($type, $isAssigned = null)
    {
        $themeId = 123;

        if ($type == \Magento\Core\Model\Theme::TYPE_VIRTUAL) {
            $theme = $this->_getVirtualThemeMock($type, $isAssigned);
        } else {
            $theme = $this->getMock('Magento\Core\Model\Theme', array('__sleep', '__wakeup'), array(), '', false);
        }

        $theme->setType($type);
        $theme->setId($themeId);

        return $theme;
    }

    /**
     * @param int $type
     * @param bool $isAssigned
     * @return \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getVirtualThemeMock($type, $isAssigned)
    {
        // 1. Get domain model
        /** @var $domainModel \Magento\Core\Model\Theme\Domain\Virtual|\PHPUnit_Framework_MockObject_MockObject */
        $domainModel = $this->getMock('Magento\Core\Model\Theme\Domain\Virtual',
            array('isAssigned'), array(), '', false);
        $domainModel->expects($this->any())
            ->method('isAssigned')
            ->will($this->returnValue($isAssigned));

        // 2. Get Theme mock
        /** @var $theme \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject */
        $theme = $this->getMock(
            'Magento\Core\Model\Theme',
            array('getDomainModel', '__sleep', '__wakeup'),
            array(),
            '',
            false
        );
        $theme->expects($this->any())
            ->method('getDomainModel')
            ->with($type)
            ->will($this->returnValue($domainModel));

        return $theme;
    }
}
