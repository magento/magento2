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
namespace Magento\UrlRewrite\Block\Edit;

/**
 * Test for \Magento\UrlRewrite\Block\Edit\FormTest
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get form instance
     *
     * @param array $args
     * @return \Magento\Framework\Data\Form
     */
    protected function _getFormInstance($args = array())
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\UrlRewrite\Block\Edit\Form */
        $block = $layout->createBlock('Magento\UrlRewrite\Block\Edit\Form', 'block', array('data' => $args));
        $block->setTemplate(null);
        $block->toHtml();
        return $block->getForm();
    }

    /**
     * Test that form was prepared correctly
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        // Test form was configured correctly
        $form = $this->_getFormInstance(array('url_rewrite' => new \Magento\Framework\Object(array('id' => 3))));
        $this->assertInstanceOf('Magento\Framework\Data\Form', $form);
        $this->assertNotEmpty($form->getAction());
        $this->assertEquals('edit_form', $form->getId());
        $this->assertEquals('post', $form->getMethod());
        $this->assertTrue($form->getUseContainer());
        $this->assertContains('/id/3', $form->getAction());

        // Check all expected form elements are present
        $expectedElements = array(
            'store_id',
            'entity_type',
            'entity_id',
            'request_path',
            'target_path',
            'redirect_type',
            'description'
        );
        foreach ($expectedElements as $expectedElement) {
            $this->assertNotNull($form->getElement($expectedElement));
        }
    }

    /**
     * Check session data restoring
     * @magentoAppIsolation enabled
     */
    public function testSessionRestore()
    {
        // Set urlrewrite data to session
        $sessionValues = array(
            'store_id' => 1,
            'entity_type' => 'entity_type',
            'entity_id' => 'entity_id',
            'request_path' => 'request_path',
            'target_path' => 'target_path',
            'redirect_type' => 'redirect_type',
            'description' => 'description'
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Session'
        )->setUrlRewriteData(
            $sessionValues
        );
        // Re-init form to use newly set session data
        $form = $this->_getFormInstance(array('url_rewrite' => new \Magento\Framework\Object()));

        // Check that all fields values are restored from session
        foreach ($sessionValues as $field => $value) {
            $this->assertEquals($value, $form->getElement($field)->getValue());
        }
    }

    /**
     * Test store element is hidden when only one store available
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     */
    public function testStoreElementSingleStore()
    {
        $form = $this->_getFormInstance(array('url_rewrite' => new \Magento\Framework\Object(array('id' => 3))));
        /** @var $storeElement \Magento\Framework\Data\Form\Element\AbstractElement */
        $storeElement = $form->getElement('store_id');
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Hidden', $storeElement);

        // Check that store value set correctly
        $defaultStore = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getStore(
            true
        )->getId();
        $this->assertEquals($defaultStore, $storeElement->getValue());
    }

    /**
     * Test store selection is available and correctly configured
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Core/_files/store.php
     */
    public function testStoreElementMultiStores()
    {
        $form = $this->_getFormInstance(array('url_rewrite' => new \Magento\Framework\Object(array('id' => 3))));
        /** @var $storeElement \Magento\Framework\Data\Form\Element\AbstractElement */
        $storeElement = $form->getElement('store_id');

        // Check store selection elements has correct type
        $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Select', $storeElement);

        // Check store selection elements has correct renderer
        $this->assertInstanceOf(
            'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element',
            $storeElement->getRenderer()
        );

        // Check store elements has expected values
        $storesList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Store\Model\System\Store'
        )->getStoreValuesForForm();
        $this->assertInternalType('array', $storeElement->getValues());
        $this->assertNotEmpty($storeElement->getValues());
        $this->assertEquals($storesList, $storeElement->getValues());
    }

    /**
     * Test fields disabled status
     * @dataProvider fieldsStateDataProvider
     * @magentoAppIsolation enabled
     */
    public function testReadonlyFields($urlRewrite, $fields)
    {
        $form = $this->_getFormInstance(array('url_rewrite' => $urlRewrite));
        foreach ($fields as $fieldKey => $expected) {
            $this->assertEquals($expected, $form->getElement($fieldKey)->getReadonly());
        }
    }

    /**
     * Data provider for checking fields state
     */
    public function fieldsStateDataProvider()
    {
        return array(
            array(
                new \Magento\Framework\Object(),
                array(
                    'store_id' => false,
                )
            ),
            array(
                new \Magento\Framework\Object(array('id' => 3, 'is_autogenerated' => true)),
                array(
                    'store_id' => true,
                )
            )
        );
    }
}
