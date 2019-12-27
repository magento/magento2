<?php
namespace Smetana\Third\Model;

use Magento\Framework\Model\AbstractModel;
use Smetana\Third\Api\Data\PartnerInterface;
use Smetana\Third\Model\ResourceModel\Partner as PartnerResource;

/**
 * Partner model class
 *
 * @package Smetana\Third\Model
 */
class Partner extends AbstractModel implements PartnerInterface
{
    /**
     * Partner model construct
     *
     * @param void
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(PartnerResource::class);
    }

    /**
     * Get product id
     *
     * @param void
     * @return string
     */
    public function getProductId(): string
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set product id
     *
     * @param string $productId
     * @return Partner
     */
    public function setProductId(string $productId): Partner
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get partner name
     *
     * @param void
     * @return string
     */
    public function getPartnerName(): string
    {
        return $this->getData(self::PARTNER_NAME);
    }

    /**
     * Set partner name
     *
     * @param string $partnerName
     * @return Partner
     */
    public function setPartnerName(string $partnerName): Partner
    {
        return $this->setData(self::PARTNER_NAME, $partnerName);
    }

    /**
     * Get partner id
     *
     * @param void
     * @return int
     */
    public function getPartnerId(): int
    {
        return (int)$this->getData(self::PARTNER_ID);
    }

    /**
     * Set partner id
     *
     * @param $partnerId
     * @return Partner
     */
    public function setPartnerId(int $partnerId): Partner
    {
        return $this->setData(self::PARTNER_ID, $partnerId);
    }
}
