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
 * Tests \Magento\Webapi\Model\Authorization\TokenUserContext
 */
class TokenUserContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Webapi\Model\Authorization\TokenUserContext
     */
    protected $tokenUserContext;

    /**
     * @var \Magento\Integration\Model\Oauth\TokenFactory
     */
    protected $tokenFactory;

    /**
     * @var \Magento\Integration\Service\V1\Integration
     */
    protected $integrationService;

    /**
     * @var \Magento\Webapi\Controller\Request
     */
    protected $request;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->request = $this->getMockBuilder('Magento\Webapi\Controller\Request')
            ->disableOriginalConstructor()
            ->setMethods(['getHeader'])
            ->getMock();

        $this->tokenFactory = $this->getMockBuilder('Magento\Integration\Model\Oauth\TokenFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->integrationService = $this->getMockBuilder('Magento\Integration\Service\V1\Integration')
            ->disableOriginalConstructor()
            ->setMethods(['findByConsumerId'])
            ->getMock();

        $this->tokenUserContext = $this->objectManager->getObject(
            'Magento\Webapi\Model\Authorization\TokenUserContext',
            [
                'request' => $this->request,
                'tokenFactory' => $this->tokenFactory,
                'integrationService' => $this->integrationService
            ]
        );
    }

    public function testNoAuthorizationHeader()
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue(null));
        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testNoTokenInHeader()
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue('Bearer'));
        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testNotBearerToken()
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue('Access'));
        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testNoTokenInDatabase()
    {
        $bearerToken = 'bearer1234';

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue("Bearer {$bearerToken}"));

        $token = $this->getMockBuilder('Magento\Integration\Model\Oauth\Token')
            ->disableOriginalConstructor()
            ->setMethods(['loadByToken', 'getId', '__wakeup'])
            ->getMock();
        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token));
        $token->expects($this->once())
            ->method('loadByToken')
            ->with($bearerToken)
            ->will($this->returnSelf());
        $token->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));

        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    public function testRevokedToken()
    {
        $bearerToken = 'bearer1234';

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue("Bearer {$bearerToken}"));

        $token = $this->getMockBuilder('Magento\Integration\Model\Oauth\Token')
            ->disableOriginalConstructor()
            ->setMethods(['loadByToken', 'getId', 'getRevoked', '__wakeup'])
            ->getMock();
        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token));
        $token->expects($this->once())
            ->method('loadByToken')
            ->with($bearerToken)
            ->will($this->returnSelf());
        $token->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $token->expects($this->once())
            ->method('getRevoked')
            ->will($this->returnValue(1));

        $this->assertNull($this->tokenUserContext->getUserType());
        $this->assertNull($this->tokenUserContext->getUserId());
    }

    /**
     * @dataProvider getValidTokenData
     */
    public function testValidToken($userType, $userId, $expectedUserType, $expectedUserId)
    {
        $bearerToken = 'bearer1234';

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue("Bearer {$bearerToken}"));

        $token = $this->getMockBuilder('Magento\Integration\Model\Oauth\Token')
            ->disableOriginalConstructor()
            ->setMethods(['loadByToken', 'getId', 'getUserType', 'getCustomerId', 'getAdminId', '__wakeup'])
            ->getMock();
        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token));
        $token->expects($this->once())
            ->method('loadByToken')
            ->with($bearerToken)
            ->will($this->returnSelf());
        $token->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $token->expects($this->once())
            ->method('getUserType')
            ->will($this->returnValue($userType));

        $integration = $this->getMockBuilder('Magento\Integration\Model\Integration')
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();

        switch($userType) {
            case UserContextInterface::USER_TYPE_INTEGRATION:
                $integration->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue($userId));
                $this->integrationService->expects($this->once())
                    ->method('findByConsumerId')
                    ->will($this->returnValue($integration));
                break;
            case UserContextInterface::USER_TYPE_ADMIN:
                $token->expects($this->once())
                    ->method('getAdminId')
                    ->will($this->returnValue($userId));
                break;
            case UserContextInterface::USER_TYPE_CUSTOMER:
                $token->expects($this->once())
                    ->method('getCustomerId')
                    ->will($this->returnValue($userId));
                break;
        }

        $this->assertEquals($expectedUserType, $this->tokenUserContext->getUserType());
        $this->assertEquals($expectedUserId, $this->tokenUserContext->getUserId());

        /* check again to make sure that the above methods were only called once */
        $this->assertEquals($expectedUserType, $this->tokenUserContext->getUserType());
        $this->assertEquals($expectedUserId, $this->tokenUserContext->getUserId());
    }

    public function getValidTokenData()
    {
        return [
            'admin token' => [
                UserContextInterface::USER_TYPE_ADMIN,
                1234,
                UserContextInterface::USER_TYPE_ADMIN,
                1234,
            ],
            'customer token' => [
                UserContextInterface::USER_TYPE_CUSTOMER,
                1234,
                UserContextInterface::USER_TYPE_CUSTOMER,
                1234,
            ],
            'integration token' => [
                UserContextInterface::USER_TYPE_INTEGRATION,
                1234,
                UserContextInterface::USER_TYPE_INTEGRATION,
                1234,
            ],
            'guest user type' => [
                UserContextInterface::USER_TYPE_GUEST,
                1234,
                null,
                null,
            ]
        ];
    }
}
