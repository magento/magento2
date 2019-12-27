<?php
namespace Smetana\Third\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Smetana\Third\Model\Partner;

/**
 * @api
 * @package Smetana\Third\Api\Data
 */
interface PartnerInterface extends ExtensibleDataInterface
{
    /**
     * Product ID column
     *
     * @var String
     */
    const PRODUCT_ID = 'product_id';

    /**
     * Partner name column
     *
     * @var String
     */
    const PARTNER_NAME = 'partner_name';

    /**
     * Partner id column
     *
     * @var String
     */
    const PARTNER_ID = 'partner_id';

    /**
     * Get product ID
     *
     * @param void
     * @return string
     */
    public function getProductId(): string;

    /**
     * Set product ID
     *
     * @param string $productId
     * @return Partner
     */
    public function setProductId(string $productId): Partner;

    /**
     * Get partner name
     *
     * @param void
     * @return string
     */
    public function getPartnerName(): string;

    /**
     * Set partner name
     *
     * @param string $partnerName
     * @return Partner
     */
    public function setPartnerName(string $partnerName): Partner;

    /**
     * Get partner id
     *
     * @param void
     * @return int
     */
    public function getPartnerId(): int;

    /**
     * Set partner id
     *
     * @param $partnerId
     * @return Partner
     */
    public function setPartnerId(int $partnerId): Partner;
}
