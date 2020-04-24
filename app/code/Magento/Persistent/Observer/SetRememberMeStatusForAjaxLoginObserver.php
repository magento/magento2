<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;

/**
 * Persistent Session Observer
 */
class SetRememberMeStatusForAjaxLoginObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    private $persistentSession;

    /**
     * @var Data
     */
    private $persistentData;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Data $persistentData
     * @param Session $persistentSession
     * @param Json $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        Data $persistentData,
        Session $persistentSession,
        Json $serializer
    ) {
        $this->persistentData = $persistentData;
        $this->persistentSession = $persistentSession;
        $this->serializer = $serializer;
    }

    /**
     * Set Checked status of "Remember Me"
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->persistentData->canProcess($observer)
            || !$this->persistentData->isEnabled()
            || !$this->persistentData->isRememberMeEnabled()
        ) {
            return;
        }

        /** @var $request RequestInterface */
        $request = $observer->getEvent()->getRequest();
        if ($request && $request->isXmlHttpRequest()) {
            $requestData = [];
            $content = $request->getContent();
            if ($content) {
                $requestData = $this->serializer->unserialize($content);
            }
            $isRememberMeChecked = empty($requestData['persistent_remember_me']) ? false : true;
            $this->persistentSession->setRememberMeChecked($isRememberMeChecked);
        }
    }
}
