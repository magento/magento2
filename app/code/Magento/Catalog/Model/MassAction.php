<?php
namespace Magento\Catalog\Model;

class MassAction implements \Magento\Catalog\Api\Data\MassActionInterface
{
    private $inventory;
    private $attributes;
    private $websiteRemove;
    private $websiteAdd;
    private $storeId;
    private $productIds;
    private $websiteId;

    /**
     * Set data value.
     *
     * @param string $data
     * @return void
     * @since 101.1.0
     */
    public function setInventory($data)
    {
        $this->inventory = $data;
    }

    /**
     * Get data value.
     *
     * @return string[]
     * @since 101.1.0
     */
    public function getInventory():array
    {
        return $this->inventory;
    }

    /**
     * Set data value.
     *
     * @param string $data
     * @return void
     * @since 101.1.0
     */
    public function setAttributes($data)
    {
        $this->attributes = $data;
    }

    /**
     * Get data value.
     *
     * @return string[]
     * @since 101.1.0
     */
    public function getAttributes():array
    {
        return $this->attributes;
    }

    /**
     * Set data value.
     *
     * @param string $data
     * @return void
     * @since 101.1.0
     */
    public function setWebsiteRemove($data)
    {
        $this->websiteRemove = $data;
    }

    /**
     * Get data value.
     *
     * @return string[]
     * @since 101.1.0
     */
    public function getWebsiteRemove():array
    {
        return $this->websiteRemove;
    }

    /**
     * Set data value.
     *
     * @param string $data
     * @return void
     * @since 101.1.0
     */
    public function setWebsiteAdd($data)
    {
        $this->websiteAdd = $data;
    }

    /**
     * Get data value.
     *
     * @return string[]
     * @since 101.1.0
     */
    public function getWebsiteAdd():array
    {
        return $this->websiteAdd;
    }

    /**
     * Set data value.
     *
     * @param string $data
     * @return void
     * @since 101.1.0
     */
    public function setStoreId($data)
    {
        $this->storeId = $data;
    }

    /**
     * Get data value.
     *
     * @return string
     * @since 101.1.0
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * Set data value.
     *
     * @param integer[] $data
     * @return void
     * @since 101.1.0
     */
    public function setProductIds(array $data)
    {
        $this->productIds = $data;
    }

    /**
     * Get data value.
     *
     * @return integer[]
     * @since 101.1.0
     */
    public function getProductIds():array
    {
        return $this->productIds;
    }

    /**
     * Set data value.
     *
     * @param string $data
     * @return void
     * @since 101.1.0
     */
    public function setWebsiteId($data)
    {
        $this->websiteId = $data;
    }

    /**
     * Get data value.
     *
     * @return string
     * @since 101.1.0
     */
    public function getWebsiteId()
    {
        return $this->websiteId;
    }
}
