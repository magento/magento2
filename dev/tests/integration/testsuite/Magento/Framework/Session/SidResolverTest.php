<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

use Zend\Stdlib\Parameters;

class SidResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Session\SidResolver
     */
    protected $model;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string
     */
    protected $customSessionName = 'csn';

    /**
     * @var string
     */
    protected $customSessionQueryParam = 'csqp';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Session\Generic _model */
        $this->session = $objectManager->get(\Magento\Framework\Session\Generic::class);

        $this->scopeConfig = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )->setMethods(
            ['getValue']
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $this->urlBuilder = $this->getMockBuilder(
            \Magento\Framework\Url::class
        )->setMethods(
            ['isOwnOriginUrl']
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $this->request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);

        $this->model = $objectManager->create(
            \Magento\Framework\Session\SidResolver::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'urlBuilder' => $this->urlBuilder,
                'sidNameMap' => [$this->customSessionName => $this->customSessionQueryParam],
                'request' => $this->request,
            ]
        );
    }

    public function tearDown()
    {
        $this->request->setQuery(new Parameters());
    }

    /**
     * @param mixed $sid
     * @param bool $useFrontedSid
     * @param bool $isOwnOriginUrl
     * @param mixed $testSid
     * @dataProvider dataProviderTestGetSid
     */
    public function testGetSid($sid, $useFrontedSid, $isOwnOriginUrl, $testSid)
    {
        $this->scopeConfig->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            \Magento\Framework\Session\SidResolver::XML_PATH_USE_FRONTEND_SID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will(
            $this->returnValue($useFrontedSid)
        );

        $this->urlBuilder->expects($this->any())->method('isOwnOriginUrl')->will($this->returnValue($isOwnOriginUrl));

        if ($testSid) {
            $this->request->getQuery()->set($this->model->getSessionIdQueryParam($this->session), $testSid);
        }
        $this->assertEquals($sid, $this->model->getSid($this->session));
    }

    /**
     * @return array
     */
    public function dataProviderTestGetSid()
    {
        return [
            [null, false, false, 'test-sid'],
            [null, false, true, 'test-sid'],
            [null, false, false, 'test-sid'],
            [null, true, false, 'test-sid'],
            [null, false, true, 'test-sid'],
            ['test-sid', true, true, 'test-sid'],
            [null, true, true, null]
        ];
    }

    public function testGetSessionIdQueryParam()
    {
        $this->assertEquals(SidResolver::SESSION_ID_QUERY_PARAM, $this->model->getSessionIdQueryParam($this->session));
    }

    public function testGetSessionIdQueryParamCustom()
    {
        $oldSessionName = $this->session->getName();
        $this->session->setName($this->customSessionName);
        $this->assertEquals($this->customSessionQueryParam, $this->model->getSessionIdQueryParam($this->session));
        $this->session->setName($oldSessionName);
    }

    public function testSetGetUseSessionVar()
    {
        $this->assertFalse($this->model->getUseSessionVar());
        $this->model->setUseSessionVar(true);
        $this->assertTrue($this->model->getUseSessionVar());
    }

    public function testSetGetUseSessionInUrl()
    {
        $this->assertTrue($this->model->getUseSessionInUrl());
        $this->model->setUseSessionInUrl(false);
        $this->assertFalse($this->model->getUseSessionInUrl());
    }
}
