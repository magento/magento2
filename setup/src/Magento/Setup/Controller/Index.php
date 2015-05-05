<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\ObjectManagerProvider;

class Index extends AbstractActionController
{
    private $objectManager;

    /**
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->objectManager = $objectManagerProvider->get();
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        if ($this->objectManager->get('Magento\Framework\App\DeploymentConfig')->isAvailable()) {

            $this->objectManager->create('Magento\Backend\Model\Auth\Session', [
                'sessionConfig' => $this->objectManager->get('Magento\Backend\Model\Session\AdminConfig')
            ]);
            if (!$this->objectManager->get('Magento\Backend\Model\Auth')->isLoggedIn()) {
                /** @var \Magento\Backend\Helper\Data $urlHelper */
                $urlHelper = $this->objectManager->get('Magento\Backend\Helper\Data');
                $url = $urlHelper->getUrl('setup/index/index', []);
                $response = $this->getPluginManager()
                    ->get('Redirect')
                    ->toUrl($url);
                $this->getEvent()->setResponse($response);

                return $response;
            }
        }
        return new ViewModel;
    }
}
