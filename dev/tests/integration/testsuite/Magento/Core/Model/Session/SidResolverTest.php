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
namespace Magento\Core\Model\Session;

class SidResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Session\SidResolver
     */
    protected $model;

    /**
     * @var \Magento\Core\Model\Session
     */
    protected $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\Store
     */
    protected $store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\Store\ConfigInterface
     */
    protected $coreStoreConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\UrlInterface
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

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Core\Model\Session _model */
        $this->session = $objectManager->get('Magento\Core\Model\Session');

        $this->coreStoreConfig = $this->getMockBuilder(
            'Magento\Core\Model\Store\ConfigInterface'
        )->setMethods(
            array('getConfig')
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $this->urlBuilder = $this->getMockBuilder(
            'Magento\Url'
        )->setMethods(
            array('isOwnOriginUrl')
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $this->model = $objectManager->create(
            'Magento\Core\Model\Session\SidResolver',
            array(
                'coreStoreConfig' => $this->coreStoreConfig,
                'urlBuilder' => $this->urlBuilder,
                'sidNameMap' => array($this->customSessionName => $this->customSessionQueryParam)
            )
        );
    }

    public function tearDown()
    {
        if (is_object($this->model) && isset($_GET[$this->model->getSessionIdQueryParam($this->session)])) {
            unset($_GET[$this->model->getSessionIdQueryParam($this->session)]);
        }
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
        $this->coreStoreConfig->expects(
            $this->any()
        )->method(
            'getConfig'
        )->with(
            SidResolver::XML_PATH_USE_FRONTEND_SID
        )->will(
            $this->returnValue($useFrontedSid)
        );

        $this->urlBuilder->expects($this->any())->method('isOwnOriginUrl')->will($this->returnValue($isOwnOriginUrl));

        if ($testSid) {
            $_GET[$this->model->getSessionIdQueryParam($this->session)] = $testSid;
        }
        $this->assertEquals($sid, $this->model->getSid($this->session));
    }

    /**
     * @return array
     */
    public function dataProviderTestGetSid()
    {
        return array(
            array(null, false, false, 'test-sid'),
            array(null, false, true, 'test-sid'),
            array(null, false, false, 'test-sid'),
            array(null, true, false, 'test-sid'),
            array(null, false, true, 'test-sid'),
            array('test-sid', true, true, 'test-sid'),
            array(null, true, true, null)
        );
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
