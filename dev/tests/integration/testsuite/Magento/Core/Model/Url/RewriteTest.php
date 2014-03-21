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
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Url;

class RewriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Url\Rewrite
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\Url\Rewrite'
        );
    }

    public function testLoadByRequestPath()
    {
        $this->_model->setStoreId(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Core\Model\StoreManagerInterface'
            )->getDefaultStoreView()->getId()
        )->setRequestPath(
            'fancy/url.html'
        )->setTargetPath(
            'catalog/product/view'
        )->setIsSystem(
            1
        )->setOptions(
            'RP'
        )->save();

        try {
            $read = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Core\Model\Url\Rewrite'
            );
            $read->setStoreId(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Core\Model\StoreManagerInterface'
                )->getDefaultStoreView()->getId()
            )->loadByRequestPath(
                'fancy/url.html'
            );

            $this->assertEquals($this->_model->getStoreId(), $read->getStoreId());
            $this->assertEquals($this->_model->getRequestPath(), $read->getRequestPath());
            $this->assertEquals($this->_model->getTargetPath(), $read->getTargetPath());
            $this->assertEquals($this->_model->getIsSystem(), $read->getIsSystem());
            $this->assertEquals($this->_model->getOptions(), $read->getOptions());
            $this->_model->delete();
        } catch (\Exception $e) {
            $this->_model->delete();
            throw $e;
        }
    }

    public function testLoadByIdPath()
    {
        $this->_model->setStoreId(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Core\Model\StoreManagerInterface'
            )->getDefaultStoreView()->getId()
        )->setRequestPath(
            'product1.html'
        )->setTargetPath(
            'catalog/product/view/id/1'
        )->setIdPath(
            'product/1'
        )->setIsSystem(
            1
        )->setOptions(
            'RP'
        )->save();

        try {
            $read = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Core\Model\Url\Rewrite'
            );
            $read->setStoreId(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Core\Model\StoreManagerInterface'
                )->getDefaultStoreView()->getId()
            )->loadByIdPath(
                'product/1'
            );
            $this->assertEquals($this->_model->getStoreId(), $read->getStoreId());
            $this->assertEquals($this->_model->getRequestPath(), $read->getRequestPath());
            $this->assertEquals($this->_model->getTargetPath(), $read->getTargetPath());
            $this->assertEquals($this->_model->getIdPath(), $read->getIdPath());
            $this->assertEquals($this->_model->getIsSystem(), $read->getIsSystem());
            $this->assertEquals($this->_model->getOptions(), $read->getOptions());
            $this->_model->delete();
        } catch (\Exception $e) {
            $this->_model->delete();
            throw $e;
        }
    }

    public function testHasOption()
    {
        $this->_model->setOptions('RP');
        $this->assertTrue($this->_model->hasOption('RP'));
    }

    public function testRewrite()
    {
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\App\RequestInterface'
        )->setPathInfo(
            'fancy/url.html'
        );
        $_SERVER['QUERY_STRING'] = 'foo=bar&___fooo=bar';

        $this->_model->setRequestPath(
            'fancy/url.html'
        )->setTargetPath(
            'another/fancy/url.html'
        )->setIsSystem(
            1
        )->save();

        try {
            $this->assertTrue($this->_model->rewrite($request));
            $this->assertEquals('/another/fancy/url.html?foo=bar', $request->getRequestUri());
            $this->assertEquals('another/fancy/url.html', $request->getPathInfo());
            $this->_model->delete();
        } catch (\Exception $e) {
            $this->_model->delete();
            throw $e;
        }
    }

    public function testRewriteNonExistingRecord()
    {
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\App\RequestInterface');
        $this->assertFalse($this->_model->rewrite($request));
    }

    public function testRewriteWrongStore()
    {
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\App\RequestInterface');
        $_GET['___from_store'] = uniqid('store');
        $this->assertFalse($this->_model->rewrite($request));
    }

    public function testRewriteNonExistingRecordCorrectStore()
    {
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\App\RequestInterface');
        $_GET['___from_store'] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Core\Model\StoreManagerInterface'
        )->getDefaultStoreView()->getCode();
        $this->assertFalse($this->_model->rewrite($request));
    }

    public function testGetStoreId()
    {
        $this->_model->setStoreId(10);
        $this->assertEquals(10, $this->_model->getStoreId());
    }

    public function testCRUD()
    {
        $this->_model->setStoreId(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Core\Model\StoreManagerInterface'
            )->getDefaultStoreView()->getId()
        )->setRequestPath(
            'fancy/url.html'
        )->setTargetPath(
            'catalog/product/view'
        )->setIsSystem(
            1
        )->setOptions(
            'RP'
        );
        $crud = new \Magento\TestFramework\Entity($this->_model, array('request_path' => 'fancy/url2.html'));
        $crud->testCrud();
    }
}
