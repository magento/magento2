<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

/**
 * Class Total
 *
 * @method string getCode()
 *
 * @api
 * @since 100.0.2
 */
class Total extends \Magento\Framework\DataObject
{
    /**
     * @var array
     */
    protected $totalAmounts = [];

    /**
     * @var array
     */
    protected $baseTotalAmounts = [];

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct($data);
    }

    /**
     * Set total amount value
     *
     * @param string $code
     * @param float $amount
     * @return $this
     */
    public function setTotalAmount($code, $amount)
    {
        $amount = is_float($amount) ? round($amount, 4) : $amount;

        $this->totalAmounts[$code] = $amount;
        if ($code != 'subtotal') {
            $code = $code . '_amount';
        }
        $this->setData($code, $amount);

        return $this;
    }

    /**
     * Set total amount value in base store currency
     *
     * @param string $code
     * @param float $amount
     * @return $this
     */
    public function setBaseTotalAmount($code, $amount)
    {
        $amount = is_float($amount) ? round($amount, 4) : $amount;

        $this->baseTotalAmounts[$code] = $amount;
        if ($code != 'subtotal') {
            $code = $code . '_amount';
        }
        $this->setData('base_' . $code, $amount);

        return $this;
    }

    /**
     * Add amount total amount value
     *
     * @param string $code
     * @param float $amount
     * @return $this
     */
    public function addTotalAmount($code, $amount)
    {
        $amount = $this->getTotalAmount($code) + $amount;
        $this->setTotalAmount($code, $amount);

        return $this;
    }

    /**
     * Add amount total amount value in base store currency
     *
     * @param string $code
     * @param float $amount
     * @return $this
     */
    public function addBaseTotalAmount($code, $amount)
    {
        $amount = $this->getBaseTotalAmount($code) + $amount;
        $this->setBaseTotalAmount($code, $amount);

        return $this;
    }

    /**
     * Get total amount value by code
     *
     * @param   string $code
     * @return  float|int
     */
    public function getTotalAmount($code)
    {
        if (isset($this->totalAmounts[$code])) {
            return $this->totalAmounts[$code];
        }

        return 0;
    }

    /**
     * Get total amount value by code in base store currency
     *
     * @param   string $code
     * @return  float|int
     */
    public function getBaseTotalAmount($code)
    {
        if (isset($this->baseTotalAmounts[$code])) {
            return $this->baseTotalAmounts[$code];
        }

        return 0;
    }

    //@codeCoverageIgnoreStart

    /**
     * Get all total amount values
     *
     * @return array
     */
    public function getAllTotalAmounts()
    {
        return $this->totalAmounts;
    }

    /**
     * Get all total amount values in base currency
     *
     * @return array
     */
    public function getAllBaseTotalAmounts()
    {
        return $this->baseTotalAmounts;
    }
    
    //@codeCoverageIgnoreEnd

    /**
     * Set the full info, which is used to capture tax related information.
     *
     * If a string is used, it is assumed to be serialized.
     *
     * @param array|string $info
     * @return $this
     * @since 100.1.0
     */
    public function setFullInfo($info)
    {
        $this->setData('full_info', $info);
        return $this;
    }

    /**
     * Returns the full info, which is used to capture tax related information.
     *
     * @return array
     * @since 100.1.0
     */
    public function getFullInfo()
    {
        $fullInfo = $this->getData('full_info');
        if (is_string($fullInfo)) {
            $fullInfo = $this->serializer->unserialize($fullInfo);
        }
        return $fullInfo;
    }
}
