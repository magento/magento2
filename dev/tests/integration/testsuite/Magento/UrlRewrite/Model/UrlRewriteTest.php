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
namespace Magento\UrlRewrite\Model;
use \Magento\TestFramework\Helper\Bootstrap;

class UrlRewriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->model = $this->objectManager->create(
            'Magento\UrlRewrite\Model\UrlRewrite'
        );
    }

    /**
     * @magentoDbIsolation enabled
     *
     * @throws \Exception
     */
    public function testLoadByRequestPath()
    {
        $this->model->setStoreId(
            $this->objectManager->get(
                'Magento\Store\Model\StoreManagerInterface'
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
            $read = $this->objectManager->create(
                'Magento\UrlRewrite\Model\UrlRewrite'
            );
            $read->setStoreId(
                $this->objectManager->get(
                    'Magento\Store\Model\StoreManagerInterface'
                )->getDefaultStoreView()->getId()
            )->loadByRequestPath(
                'fancy/url.html'
            );

            $this->assertEquals($this->model->getStoreId(), $read->getStoreId());
            $this->assertEquals($this->model->getRequestPath(), $read->getRequestPath());
            $this->assertEquals($this->model->getTargetPath(), $read->getTargetPath());
            $this->assertEquals($this->model->getIsSystem(), $read->getIsSystem());
            $this->assertEquals($this->model->getOptions(), $read->getOptions());
            $this->model->delete();
        } catch (\Exception $e) {
            $this->model->delete();
            throw $e;
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @throws \Exception
     */
    public function testLoadByIdPath()
    {
        $this->model->setStoreId(
            $this->objectManager->get(
                'Magento\Store\Model\StoreManagerInterface'
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
            $read = $this->objectManager->create(
                'Magento\UrlRewrite\Model\UrlRewrite'
            );
            $read->setStoreId(
                $this->objectManager->get(
                    'Magento\Store\Model\StoreManagerInterface'
                )->getDefaultStoreView()->getId()
            )->loadByIdPath(
                'product/1'
            );
            $this->assertEquals($this->model->getStoreId(), $read->getStoreId());
            $this->assertEquals($this->model->getRequestPath(), $read->getRequestPath());
            $this->assertEquals($this->model->getTargetPath(), $read->getTargetPath());
            $this->assertEquals($this->model->getIdPath(), $read->getIdPath());
            $this->assertEquals($this->model->getIsSystem(), $read->getIsSystem());
            $this->assertEquals($this->model->getOptions(), $read->getOptions());
            $this->model->delete();
        } catch (\Exception $e) {
            $this->model->delete();
            throw $e;
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testHasOption()
    {
        $this->model->setOptions('RP');
        $this->assertTrue($this->model->hasOption('RP'));
    }

    /**
     *
     * @magentoDbIsolation enabled
     * @throws \Exception
     */
    public function testRewrite()
    {
        $request = $this->objectManager->create(
            'Magento\Framework\App\RequestInterface'
        )->setPathInfo(
            'fancy/url.html'
        );
        $_SERVER['QUERY_STRING'] = 'foo=bar&___fooo=bar';

        $this->model->setRequestPath(
            'fancy/url.html'
        )->setTargetPath(
            'another/fancy/url.html'
        )->setIsSystem(
            1
        )->save();

        try {
            $this->assertTrue($this->model->rewrite($request));
            $this->assertEquals('/another/fancy/url.html?foo=bar', $request->getRequestUri());
            $this->assertEquals('another/fancy/url.html', $request->getPathInfo());
            $this->model->delete();
        } catch (\Exception $e) {
            $this->model->delete();
            throw $e;
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testRewriteSetCookie()
    {
        $_SERVER['QUERY_STRING'] = 'foo=bar';

        $context = $this->objectManager->create(
            '\Magento\Framework\Model\Context'
        );
        $registry = $this->objectManager->create(
            '\Magento\Framework\Registry'
        );
        $scopeConfig = $this->objectManager->create(
            '\Magento\Framework\App\Config\ScopeConfigInterface'
        );
        $cookieMetadataFactory = $this->objectManager->create(
            '\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory'
        );
        $cookieManager = $this->objectManager->create(
            '\Magento\Framework\Stdlib\CookieManager'
        );
        $storeManager = $this->objectManager->create(
            '\Magento\Store\Model\StoreManagerInterface'
        );
        $httpContext = $this->objectManager->create(
            '\Magento\Framework\App\Http\Context'
        );

        $constructorArgs = [
            'context' => $context,
            'registry' => $registry,
            'scopeConfig' => $scopeConfig,
            'cookieMetadataFactory' => $cookieMetadataFactory,
            'cookieManager' => $cookieManager,
            'storeManager' => $storeManager,
            'httpContext' => $httpContext,
        ];

        //SUT must be mocked out for this test to prevent headers from being sent,
        //causing errors.

        /** @var \PHPUnit_Framework_MockObject_MockObject /\Magento\UrlRewrite\Model\UrlRewrite $modelMock */
        $modelMock = $this->getMock('\Magento\UrlRewrite\Model\UrlRewrite',
            ['_sendRedirectHeaders'],
            $constructorArgs
        );

        $modelMock->setRequestPath('http://fancy/url.html')
            ->setTargetPath('http://another/fancy/url.html')
            ->setIsSystem(1)
            ->setOptions('R')
            ->save();

        $modelMock->expects($this->exactly(2))
            ->method('_sendRedirectHeaders');

        $request = $this->objectManager
            ->create('Magento\Framework\App\RequestInterface')
            ->setPathInfo('http://fancy/url.html');

        $this->assertTrue($modelMock->rewrite($request));
        $this->assertEquals('admin', $_COOKIE[\Magento\Store\Model\Store::COOKIE_NAME]);

        $modelMock->delete();

    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testRewriteNonExistingRecord()
    {
        $request = $this->objectManager
            ->create('Magento\Framework\App\RequestInterface');
        $this->assertFalse($this->model->rewrite($request));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testRewriteWrongStore()
    {
        $request = $this->objectManager
            ->create('Magento\Framework\App\RequestInterface');
        $_GET['___from_store'] = uniqid('store');
        $this->assertFalse($this->model->rewrite($request));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testRewriteNonExistingRecordCorrectStore()
    {
        $request = $this->objectManager
            ->create('Magento\Framework\App\RequestInterface');
        $_GET['___from_store'] = $this->objectManager->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getDefaultStoreView()->getCode();
        $this->assertFalse($this->model->rewrite($request));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetStoreId()
    {
        $this->model->setStoreId(10);
        $this->assertEquals(10, $this->model->getStoreId());
    }

    /**
     * @magentoDbIsolation enabled
     *
     * @throws \Exception
     */
    public function testCRUD()
    {
        $this->model->setStoreId(
            $this->objectManager->get(
                'Magento\Store\Model\StoreManagerInterface'
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
        $crud = new \Magento\TestFramework\Entity($this->model, ['request_path' => 'fancy/url2.html']);
        $crud->testCrud();
    }
}
