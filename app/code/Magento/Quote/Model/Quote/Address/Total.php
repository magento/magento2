<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

class Total extends \Magento\Framework\DataObject
{
    /**
     * @var array
     */
    protected $totalAmounts;

    /**
     * @var array
     */
    protected $baseTotalAmounts;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param array $data [optional]
     * @param \Magento\Framework\Serialize\SerializerInterface|null $serializer [optional]
     */
    public function __construct(
        array $data = [],
        \Magento\Framework\Serialize\SerializerInterface $serializer = null
    ) {
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\SerializerInterface::class);
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
     * If a string is used, it is assumed to be serialized.
     *
     * @param array|string $info
     * @return $this
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
