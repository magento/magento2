<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model\Directpost;

use Magento\Framework\Session\SessionManager;

/**
 * Authorize.net DirectPost session model
 */
class Session extends SessionManager
{
    /**
     * Add order IncrementId to session
     *
     * @param string $orderIncrementId
     * @return void
     */
    public function addCheckoutOrderIncrementId($orderIncrementId)
    {
        $orderIncIds = $this->getDirectPostOrderIncrementIds();
        if (!$orderIncIds) {
            $orderIncIds = [];
        }
        $orderIncIds[$orderIncrementId] = 1;
        $this->setDirectPostOrderIncrementIds($orderIncIds);
    }

    /**
     * Remove order IncrementId from session
     *
     * @param string $orderIncrementId
     * @return void
     */
    public function removeCheckoutOrderIncrementId($orderIncrementId)
    {
        $orderIncIds = $this->getDirectPostOrderIncrementIds();

        if (!is_array($orderIncIds)) {
            return;
        }

        if (isset($orderIncIds[$orderIncrementId])) {
            unset($orderIncIds[$orderIncrementId]);
        }
        $this->setDirectPostOrderIncrementIds($orderIncIds);
    }

    /**
     * Return if order incrementId is in session.
     *
     * @param string $orderIncrementId
     * @return bool
     */
    public function isCheckoutOrderIncrementIdExist($orderIncrementId)
    {
        $orderIncIds = $this->getDirectPostOrderIncrementIds();
        if (is_array($orderIncIds) && isset($orderIncIds[$orderIncrementId])) {
            return true;
        }
        return false;
    }

    /**
     * Set quote id to session
     *
     * @param int|string $id
     * @return $this
     */
    public function setQuoteId($id)
    {
        $this->storage->setQuoteId($id);
        return $this;
    }
}
