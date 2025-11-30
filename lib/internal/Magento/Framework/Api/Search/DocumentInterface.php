<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * Interface Search Document
 *
 * @api
 */
interface DocumentInterface extends CustomAttributesDataInterface
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
