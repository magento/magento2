<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ObserverInterface;

/**
 *  Delete the url key of a product on product deletion
 */
class DeleteUrlKeys implements ObserverInterface
{
    /** @var ResourceConnection $resourceConnection */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
        )
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Delete the product url key
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $ids = $observer->getEvent()->getProducts();
        $connection = $this->resourceConnection->getConnection();

        try
        {
            $connection->delete('url_rewrite', ['entity_type = ?' => 'product', 'entity_id IN (?)' => $ids]);
        }
        catch (\Exception $exception)
        {
            throw new CouldNotDeleteException(__(
                'Could not delete the url rewrite(s): %1',
                $exception->getMessage()
            ));
        }

        return $this;
    }
}
