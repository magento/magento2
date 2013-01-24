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
 * @package     Mage_DesignEditor
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * All controller actions must be run with logged in admin user
 */
class Mage_DesignEditor_PageControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * Default page type url
     */
    const PAGE_TYPE_URL = 'design/page/type';

    /**
     * VDE front name prefix
     */
    const VDE_FRONT_NAME = 'vde_front_name';

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Test handles
     *
     * @var array
     */
    protected $_testHandles = array(
        'incorrect'    => '123!@#',
        'not_existing' => 'not_existing_handle',
        'default'      => 'default',
        'correct'      => 'cms_index_index',
    );

    public function setUp()
    {
        parent::setUp();

        $this->_objectManager = Mage::getObjectManager();
    }

    /**
     * Method preDispatch forwards to noRoute action if user is not logged in admin area
     */
    public function testPreDispatch()
    {
        $this->_auth->logout();

        $this->dispatch(self::PAGE_TYPE_URL);

        $this->assertEquals('noRoute', $this->getRequest()->getActionName());
    }

    /**
     * Exception cases in typeAction method
     *
     * @magentoConfigFixture vde/design_editor/frontName vde_front_name
     */
    public function testTypeActionIncorrectLayout()
    {
        $this->getRequest()->setParam('handle', $this->_testHandles['correct']);
        $this->dispatch(self::PAGE_TYPE_URL);

        $response = $this->getResponse();
        $this->assertEquals(503, $response->getHttpResponseCode());
        $this->assertEquals('Incorrect Design Editor layout.', $response->getBody());
    }

    /**
     * @param $actualHandle
     * @param $expectedHandle
     *
     * @dataProvider typeActionDataProvider
     * @magentoConfigFixture vde/design_editor/frontName vde_front_name
     * @magentoConfigFixture vde/design_editor/defaultHandle default
     */
    public function testTypeAction($actualHandle, $expectedHandle)
    {
        $this->getRequest()->setParam('handle', $actualHandle);
        $this->dispatch(self::VDE_FRONT_NAME . '/' . self::PAGE_TYPE_URL);

        // assert layout data
        /** @var $layout Mage_Core_Model_Layout */
        $layout = $this->_objectManager->get('Mage_Core_Model_Layout');
        $handles = $layout->getUpdate()->getHandles();
        $this->assertContains($expectedHandle, $handles);
        $this->assertContains('designeditor_page_type', $handles);
        $this->assertAttributeSame(true, '_sanitationEnabled', $layout);
        $this->assertAttributeSame(true, '_wrappingEnabled', $layout);

        // assert response body
        $responseBody = $this->getResponse()->getBody();
        $this->assertContains('class="vde_element_wrapper', $responseBody); // enabled wrapper
        $this->assertContains('/css/design.css', $responseBody);            // included wrapper CSS
    }

    /**
     * Data provider for testTypeAction
     *
     * @return array
     */
    public function typeActionDataProvider()
    {
        return array(
            'invalid_handle' => array(
                '$actualHandle'   => $this->_testHandles['incorrect'],
                '$expectedHandle' => $this->_testHandles['default'],
            ),
            'not_existing_handle' => array(
                '$actualHandle'   => $this->_testHandles['not_existing'],
                '$expectedHandle' => $this->_testHandles['default'],
            ),
            'correct_handle' => array(
                '$actualHandle'   => $this->_testHandles['correct'],
                '$expectedHandle' => $this->_testHandles['correct'],
            ),
        );
    }
}
