<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Model\Directpost;

use Magento\Framework\Session\SessionManager;

/**
 * Authorize.net DirectPost session model
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method in July 2019
 */
class Session extends SessionManager
{
    /**
     * Add order IncrementId to session
     *
     * @param string $orderIncrementId
     * @return void
     * @deprecated
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
     * @deprecated
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
     * @deprecated
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
     * @deprecated
     */
    public function setQuoteId($id)
    {
        $this->storage->setQuoteId($id);
        return $this;
    }
}
