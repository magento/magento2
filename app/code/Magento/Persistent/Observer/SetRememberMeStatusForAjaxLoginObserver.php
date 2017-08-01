<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Persistent Session Observer
 * @since 2.0.0
 */
class SetRememberMeStatusForAjaxLoginObserver implements ObserverInterface
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     * @since 2.0.0
     */
    protected $_persistentSession;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.0.0
     */
    protected $_persistentData = null;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * SetRememberMeStatusForAjaxLoginObserver constructor.
     *
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_persistentData = $persistentData;
        $this->_persistentSession = $persistentSession;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Set Checked status of "Remember Me"
     *
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        if (!$this->_persistentData->canProcess($observer)
            || !$this->_persistentData->isEnabled()
            || !$this->_persistentData->isRememberMeEnabled()
        ) {
            return;
        }

        /** @var $request \Magento\Framework\App\RequestInterface */
        $request = $observer->getEvent()->getRequest();
        if ($request && $request->isXmlHttpRequest()) {
            $requestData = [];
            $content = $request->getContent();
            if ($content) {
                $requestData = $this->serializer->unserialize($content);
            }
            $isRememberMeChecked = empty($requestData['persistent_remember_me']) ? false : true;
            $this->_persistentSession->setRememberMeChecked((bool)$isRememberMeChecked);
        }
    }
}
