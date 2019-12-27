<?php
namespace Smetana\Third\Model\ResourceModel\Partner;

use Smetana\Third\Model\Partner;
use Smetana\Third\Model\ResourceModel\Partner as PartnerResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Partner collection class
 *
 * @package Smetana\Third\Model\ResourceModel\Partner
 */
class Collection extends AbstractCollection
{
    /**
     * Collection initialization
     *
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(
            Partner::class,
            PartnerResource::class
        );
    }
}
