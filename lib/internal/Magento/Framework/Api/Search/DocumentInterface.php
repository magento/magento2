<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * Interface \Magento\Framework\Api\Search\DocumentInterface
 *
 * @since 2.0.0
 */
interface DocumentInterface extends CustomAttributesDataInterface
{
    const ID = 'id';

    /**
     * @return int
     * @since 2.0.0
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);
}
