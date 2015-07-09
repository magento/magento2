<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Grid;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Object;

/**
 * Class Document
 */
class Document extends Object implements DocumentInterface
{
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}
