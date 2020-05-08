<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rss\Test\Unit\App\Action\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\HTTP\Authentication;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Rss\App\Action\Plugin\BackendAuthentication;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BackendAuthenticationTest extends TestCase
{
    public function testAroundDispatch()
    {
        /** @var AbstractAction|MockObject $subject */
        $subject = $this->createMock(AbstractAction::class);

        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);

        $proceed = function () use ($response) {
            return $response;
        };

        /** @var Http|MockObject $request */
        $request = $this->createMock(Http::class);
        $request->expects($this->atLeastOnce())->method('getControllerName')->willReturn('feed');
        $request->expects($this->atLeastOnce())->method('getActionName')->willReturn('index');
        $request->expects($this->once())->method('getParam')->with('type')->willReturn('notifystock');

        /** @var StorageInterface|MockObject $session */
        $session = $this->getMockForAbstractClass(StorageInterface::class);
        $session->expects($this->at(0))->method('isLoggedIn')->willReturn(false);
        $session->expects($this->at(1))->method('isLoggedIn')->willReturn(true);

        $username = 'admin';
        $password = '123123qa';
        $auth = $this->createMock(Auth::class);
        $auth->expects($this->once())->method('getAuthStorage')->willReturn($session);
        $auth->expects($this->once())->method('login')->with($username, $password);

        /** @var Authentication|MockObject $httpAuthentication */
        $httpAuthentication = $this->createMock(Authentication::class);
        $httpAuthentication->expects($this->once())->method('getCredentials')
            ->willReturn([$username, $password]);
        $httpAuthentication->expects($this->once())->method('setAuthenticationFailed')->with('RSS Feeds');

        $authorization = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $authorization->expects($this->at(0))->method('isAllowed')->with('Magento_Rss::rss')
            ->willReturn(true);
        $authorization->expects($this->at(1))->method('isAllowed')->with('Magento_Catalog::catalog_inventory')
            ->willReturn(false);

        $aclResources = [
            'feed' => 'Magento_Rss::rss',
            'notifystock' => 'Magento_Catalog::catalog_inventory',
            'new_order' => 'Magento_Sales::actions_view',
            'review' => 'Magento_Reports::review_product'
        ];

        /** @var BackendAuthentication $plugin */
        $plugin = (new ObjectManager($this))
            ->getObject(
                BackendAuthentication::class,
                [
                    'auth' => $auth,
                    'httpAuthentication' => $httpAuthentication,
                    'response' => $response,
                    'authorization' => $authorization,
                    'aclResources' => $aclResources
                ]
            );
        $this->assertSame(
            $response,
            $plugin->aroundDispatch($subject, $proceed, $request)
        );
    }
}
