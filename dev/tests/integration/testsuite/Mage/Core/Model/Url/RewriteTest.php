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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Url_RewriteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Url_Rewrite
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Core_Model_Url_Rewrite;
    }

    public function testLoadByRequestPath()
    {
        $this->_model->setStoreId(Mage::app()->getDefaultStoreView()->getId())
            ->setRequestPath('fancy/url.html')
            ->setTargetPath('catalog/product/view')
            ->setIsSystem(1)
            ->setOptions('RP')
            ->save();

        try {
            $read = new Mage_Core_Model_Url_Rewrite;
            $read->setStoreId(Mage::app()->getDefaultStoreView()->getId())
                ->loadByRequestPath('fancy/url.html');

            $this->assertEquals($this->_model->getStoreId(), $read->getStoreId());
            $this->assertEquals($this->_model->getRequestPath(), $read->getRequestPath());
            $this->assertEquals($this->_model->getTargetPath(), $read->getTargetPath());
            $this->assertEquals($this->_model->getIsSystem(), $read->getIsSystem());
            $this->assertEquals($this->_model->getOptions(), $read->getOptions());
            $this->_model->delete();
        } catch (Exception $e) {
            $this->_model->delete();
            throw $e;
        }
    }

    public function testLoadByIdPath()
    {
        $this->_model->setStoreId(Mage::app()->getDefaultStoreView()->getId())
            ->setRequestPath('product1.html')
            ->setTargetPath('catalog/product/view/id/1')
            ->setIdPath('product/1')
            ->setIsSystem(1)
            ->setOptions('RP')
            ->save();

        try {
            $read = new Mage_Core_Model_Url_Rewrite;
            $read->setStoreId(Mage::app()->getDefaultStoreView()->getId())
                ->loadByIdPath('product/1');
            $this->assertEquals($this->_model->getStoreId(), $read->getStoreId());
            $this->assertEquals($this->_model->getRequestPath(), $read->getRequestPath());
            $this->assertEquals($this->_model->getTargetPath(), $read->getTargetPath());
            $this->assertEquals($this->_model->getIdPath(), $read->getIdPath());
            $this->assertEquals($this->_model->getIsSystem(), $read->getIsSystem());
            $this->assertEquals($this->_model->getOptions(), $read->getOptions());
            $this->_model->delete();
        } catch (Exception $e) {
            $this->_model->delete();
            throw $e;
        }
    }

    public function testHasOption()
    {
        $this->_model->setOptions('RP');
        $this->assertTrue($this->_model->hasOption('RP'));
    }

    /**
     * Warning: this test is not isolated from other tests (Mage::app()->getRequest() and getResponse())
     */
    public function testRewrite()
    {
        $request = Mage::app()->getRequest()->setPathInfo('fancy/url.html');
        $response = new Magento_Test_Response();
        $_SERVER['QUERY_STRING'] = 'foo=bar&___fooo=bar';

        $this->_model->setRequestPath('fancy/url.html')
            ->setTargetPath('another/fancy/url.html')
            ->setIsSystem(1)
            ->save();

        try {
            $this->assertTrue($this->_model->rewrite(null, $response));
            $this->assertEquals('/another/fancy/url.html?foo=bar', $request->getRequestUri());
            $this->assertEquals('another/fancy/url.html', $request->getPathInfo());
            $this->_model->delete();
        } catch (Exception $e) {
            $this->_model->delete();
            throw $e;
        }
    }

    public function testRewriteNonExistingRecord()
    {
        $response = new Magento_Test_Response();
        $this->assertFalse($this->_model->rewrite(null, $response));
    }

    public function testRewriteWrongStore()
    {
        $response = new Magento_Test_Response();
        $_GET['___from_store'] = uniqid('store');
        $this->assertFalse($this->_model->rewrite(null, $response));
    }

    public function testRewriteNonExistingRecordCorrectStore()
    {
        $response = new Magento_Test_Response();
        $_GET['___from_store'] = Mage::app()->getDefaultStoreView()->getCode();
        $this->assertFalse($this->_model->rewrite(null, $response));
    }

    public function testGetStoreId()
    {
        $this->_model->setStoreId(10);
        $this->assertEquals(10, $this->_model->getStoreId());
    }

    public function testCRUD()
    {
        $this->_model->setStoreId(Mage::app()->getDefaultStoreView()->getId())
            ->setRequestPath('fancy/url.html')
            ->setTargetPath('catalog/product/view')
            ->setIsSystem(1)
            ->setOptions('RP')
        ;
        $crud = new Magento_Test_Entity($this->_model, array('request_path' => 'fancy/url2.html'));
        $crud->testCrud();
    }
}
