<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Test\Unit\App\Action\Plugin;

class BackendAuthenticationTest extends \PHPUnit\Framework\TestCase
{
    public function testAroundDispatch()
    {
        /** @var \Magento\Backend\App\AbstractAction|\PHPUnit\Framework\MockObject\MockObject $subject */
        $subject = $this->createMock(\Magento\Backend\App\AbstractAction::class);

        /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject $response */
        $response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);

        $proceed = function () use ($response) {
            return $response;
        };

        /** @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject $request */
        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $request->expects($this->atLeastOnce())->method('getControllerName')->willReturn('feed');
        $request->expects($this->atLeastOnce())->method('getActionName')->willReturn('index');
        $request->expects($this->once())->method('getParam')->with('type')->willReturn('notifystock');

        /** @var \Magento\Backend\Model\Auth\StorageInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(\Magento\Backend\Model\Auth\StorageInterface::class);
        $session->expects($this->at(0))->method('isLoggedIn')->willReturn(false);
        $session->expects($this->at(1))->method('isLoggedIn')->willReturn(true);

        $username = 'admin';
        $password = '123123qa';
        $auth = $this->createMock(\Magento\Backend\Model\Auth::class);
        $auth->expects($this->once())->method('getAuthStorage')->willReturn($session);
        $auth->expects($this->once())->method('login')->with($username, $password);

        /** @var \Magento\Framework\HTTP\Authentication|\PHPUnit\Framework\MockObject\MockObject $httpAuthentication */
        $httpAuthentication = $this->createMock(\Magento\Framework\HTTP\Authentication::class);
        $httpAuthentication->expects($this->once())->method('getCredentials')
            ->willReturn([$username, $password]);
        $httpAuthentication->expects($this->once())->method('setAuthenticationFailed')->with('RSS Feeds');

        $authorization = $this->createMock(\Magento\Framework\AuthorizationInterface::class);
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

        /** @var \Magento\Rss\App\Action\Plugin\BackendAuthentication $plugin */
        $plugin = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Rss\App\Action\Plugin\BackendAuthentication::class,
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
