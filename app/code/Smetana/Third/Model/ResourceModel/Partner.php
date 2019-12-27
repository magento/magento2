<?php
namespace Smetana\Third\Model\ResourceModel;

use Smetana\Third\Api\Data\PartnerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Partner resource model class
 *
 * @package Smetana\Third\Model\ResourceModel
 */
class Partner extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('smetana_partner', PartnerInterface::PARTNER_ID);
    }
}
