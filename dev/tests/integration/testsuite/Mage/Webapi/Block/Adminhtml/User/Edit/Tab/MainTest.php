<?php
/**
 * Test for Mage_Webapi_Block_Adminhtml_User_Edit_Tab_Main block.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Block_Adminhtml_User_Edit_Tab_MainTest extends Mage_Backend_Area_TestCase
{
    /**
     * @var Magento_Test_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    /**
     * @var Mage_Core_Model_BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var Mage_Webapi_Block_Adminhtml_User_Edit_Tab_Main
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        $this->_objectManager = Mage::getObjectManager();
        $this->_layout = $this->_objectManager->get('Mage_Core_Model_Layout');
        $this->_blockFactory = $this->_objectManager->get('Mage_Core_Model_BlockFactory');
        $this->_block = $this->_blockFactory->createBlock('Mage_Webapi_Block_Adminhtml_User_Edit_Tab_Main');
        $this->_layout->addBlock($this->_block);
    }

    protected function tearDown()
    {
        $this->_objectManager->removeSharedInstance('Mage_Core_Model_Layout');
        unset($this->_objectManager, $this->_urlBuilder, $this->_layout, $this->_blockFactory, $this->_block);
    }

    /**
     * Test _prepareForm method.
     *
     * @dataProvider prepareFormDataProvider
     * @param Varien_Object $apiUser
     * @param array $formElements
     */
    public function testPrepareForm($apiUser, array $formElements)
    {
        // TODO: Move to unit tests after MAGETWO-4015 complete.
        $this->assertEmpty($this->_block->getForm());

        $this->_block->setApiUser($apiUser);
        $this->_block->toHtml();

        $form = $this->_block->getForm();
        $this->assertInstanceOf('Varien_Data_Form', $form);
        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $form->getElement('base_fieldset');
        $this->assertInstanceOf('Varien_Data_Form_Element_Fieldset', $fieldset);
        $elements = $fieldset->getElements();
        foreach ($formElements as $elementId) {
            $element = $elements->searchById($elementId);
            $this->assertNotEmpty($element, "Element '$elementId' is not found in form fieldset");
            $this->assertEquals($apiUser->getData($elementId), $element->getValue());
        }
    }

    /**
     * @return array
     */
    public function prepareFormDataProvider()
    {
        return array(
            'Empty API User' => array(
                new Varien_Object(),
                array(
                    'company_name',
                    'contact_email',
                    'api_key',
                    'secret'
                )
            ),
            'New API User' => array(
                new Varien_Object(array(
                    'company_name' => 'Company',
                    'contact_email' => 'mail@example.com',
                    'api_key' => 'API Key',
                    'secret' => 'API Secret',
                    'role_id' => 1
                )),
                array(
                    'company_name',
                    'contact_email',
                    'api_key',
                    'secret'
                )
            ),
            'Existed API User' => array(
                new Varien_Object(array(
                    'id' => 1,
                    'company_name' => 'Company',
                    'contact_email' => 'mail@example.com',
                    'api_key' => 'API Key',
                    'secret' => 'API Secret',
                    'role_id' => 1
                )),
                array(
                    'user_id',
                    'company_name',
                    'contact_email',
                    'api_key',
                    'secret'
                )
            )
        );
    }
}
