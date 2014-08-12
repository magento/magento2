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

namespace Magento\Webapi\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Tests \Magento\Webapi\Model\Authorization\OauthUserContext
 */
class OauthUserContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Webapi\Model\Authorization\OauthUserContext
     */
    protected $oauthUserContext;

    /**
     * @var \Magento\Webapi\Controller\Request
     */
    protected $request;

    /**
     * @var \Magento\Framework\Oauth\Helper\Request
     */
    protected $oauthRequestHelper;

    /**
     * @var \Magento\Integration\Service\V1\Integration
     */
    protected $integrationService;

    /**
     * @var \Magento\Framework\Oauth\Oauth
     */
    protected $oauthService;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->request = $this->getMockBuilder('Magento\Webapi\Controller\Request')
            ->disableOriginalConstructor()
            ->setMethods(['getConsumerId'])
            ->getMock();

        $this->integrationService = $this->getMockBuilder('Magento\Integration\Service\V1\Integration')
            ->disableOriginalConstructor()
            ->setMethods(['findActiveIntegrationByConsumerId'])
            ->getMock();

        $this->oauthRequestHelper = $this->getMockBuilder('Magento\Framework\Oauth\Helper\Request')
            ->disableOriginalConstructor()
            ->setMethods(['prepareRequest'])
            ->getMock();

        $this->oauthService = $this->getMockBuilder('Magento\Framework\Oauth\Oauth')
            ->disableOriginalConstructor()
            ->setMethods(['validateAccessTokenRequest'])
            ->getMock();

        $this->oauthUserContext = $this->objectManager->getObject(
            'Magento\Webapi\Model\Authorization\OauthUserContext',
            [
                'request' => $this->request,
                'integrationService' => $this->integrationService,
                'oauthService' => $this->oauthService,
                'oauthHelper' => $this->oauthRequestHelper
            ]
        );
    }

    public function testGetUserType()
    {
        $this->assertEquals(UserContextInterface::USER_TYPE_INTEGRATION, $this->oauthUserContext->getUserType());
    }

    public function testGetUserIdExist()
    {
        $integrationId = 12345;

        $this->setupUserId($integrationId, ['oauth_token' => 'asdcfsdvanskdcalkdsjcfljldk']);

        $this->assertEquals($integrationId, $this->oauthUserContext->getUserId());
    }

    public function testGetUserIdDoesNotExist()
    {
        $integrationId = null;

        $this->setupUserId($integrationId, ['oauth_token' => 'asdcfsdvanskdcalkdsjcfljldk']);

        $this->assertEquals($integrationId, $this->oauthUserContext->getUserId());
    }

    public function testGetUserIdNoOauthInformation()
    {
        $integrationId = 12345;

        $this->setupUserId($integrationId, []);

        $this->assertEquals(null, $this->oauthUserContext->getUserId());
    }

    /**
     * @param int|null $integrationId
     * @param array $oauthRequest
     * @return void
     */
    public function setupUserId($integrationId, $oauthRequest)
    {
        $integration = $this->getMockBuilder('Magento\Integration\Model\Integration')
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();

        $this->integrationService->expects($this->any())
            ->method('findActiveIntegrationByConsumerId')
            ->will($this->returnValue($integration));

        $this->oauthRequestHelper->expects($this->once())
            ->method('prepareRequest')
            ->will($this->returnValue($oauthRequest));

        $this->oauthService->expects($this->any())
            ->method('validateAccessTokenRequest')
            ->will($this->returnValue(1));

        $integration->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($integrationId));
    }
}
