<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Test\Unit\App\Action\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Rss\App\Action\Plugin\BackendAuthentication;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BackendAuthenticationTest extends TestCase
{
    /**
     * @TODO Cover plugin with real tests
     */
    public function testAroundExecute()
    {
        /** @var ActionInterface|MockObject $subject */
        $subject = $this->createMock(ActionInterface::class);

        /** @var \Magento\Framework\App\ResponseInterface|MockObject $response */
        $response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);

        $proceed = function () use ($response) {
            return $response;
        };

        /** @var \Magento\Framework\App\Request\Http|MockObject $request */
        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $request->expects($this->atLeastOnce())->method('getControllerName')->will($this->returnValue('feed'));
        $request->expects($this->atLeastOnce())->method('getActionName')->will($this->returnValue('index'));
        $request->expects($this->once())->method('getParam')->with('type')->will($this->returnValue('notifystock'));

        /** @var \Magento\Backend\Model\Auth\StorageInterface|MockObject $session */
        $session = $this->createMock(\Magento\Backend\Model\Auth\StorageInterface::class);
        $session->expects($this->at(0))->method('isLoggedIn')->will($this->returnValue(false));
        $session->expects($this->at(1))->method('isLoggedIn')->will($this->returnValue(true));

        $username = 'admin';
        $password = '123123qa';
        $auth = $this->createMock(\Magento\Backend\Model\Auth::class);
        $auth->expects($this->once())->method('getAuthStorage')->will($this->returnValue($session));
        $auth->expects($this->once())->method('login')->with($username, $password);

        /** @var \Magento\Framework\HTTP\Authentication|MockObject $httpAuthentication */
        $httpAuthentication = $this->createMock(\Magento\Framework\HTTP\Authentication::class);
        $httpAuthentication->expects($this->once())->method('getCredentials')
            ->will($this->returnValue([$username, $password]));
        $httpAuthentication->expects($this->once())->method('setAuthenticationFailed')->with('RSS Feeds');

        $authorization = $this->createMock(\Magento\Framework\AuthorizationInterface::class);
        $authorization->expects($this->at(0))->method('isAllowed')->with('Magento_Rss::rss')
            ->will($this->returnValue(true));
        $authorization->expects($this->at(1))->method('isAllowed')->with('Magento_Catalog::catalog_inventory')
            ->will($this->returnValue(false));

        $aclResources = [
            'feed' => 'Magento_Rss::rss',
            'notifystock' => 'Magento_Catalog::catalog_inventory',
            'new_order' => 'Magento_Sales::actions_view',
            'review' => 'Magento_Reports::review_product'
        ];

        /** @var BackendAuthentication $plugin */
        $plugin = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                BackendAuthentication::class,
                [
                    'request' => $request,
                    'auth' => $auth,
                    'httpAuthentication' => $httpAuthentication,
                    'response' => $response,
                    'authorization' => $authorization,
                    'aclResources' => $aclResources
                ]
            );
        $this->assertSame(
            $response,
            $plugin->aroundExecute($subject, $proceed)
        );
    }
}
