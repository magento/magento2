<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

/**
 * Interface DocumentInterface
 * @package Magento\Framework\Api\Search
 */
interface DocumentInterface
{
    const ID = 'id';
    /**
     * @return int
     */
    public function getId();
    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);
}
