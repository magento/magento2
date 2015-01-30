<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Eav\Model\Config as EavConfig;

/**
 * Class Increment
 */
class Increment
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var string
     */
    protected $incrementValue;

    /**
     * @var string
     */
	protected $entityType;

    /**
     * @param EavConfig $eavConfig
     */
    public function __construct(
        EavConfig $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Returns current increment id
     *
     * @return string
     */
    public function getCurrentValue()
    {
        return $this->incrementValue;
    }
	
	/*
	* Sets the entity type
	* @param string $entityType
	* @return Magento\Sales\Model\Increment
	*/
	public function setEntityType($entityType){
		$this->entityType = $entityType;
		return $this;
	}

    /**
     * Returns new value of increment id
     *
     * @param int $storeId
     * @return string
     * @throws \Exception
     * @throws \Magento\Framework\Model\Exception
     */
    public function getNextValue($storeId)
    {
		if(empty($this->entityType)){
			throw new \Magento\Framework\Model\Exception(
				__('EntityType is empty')
			);
		}
        $this->incrementValue =
            $this->eavConfig->getEntityType($this->entityType)->fetchNewIncrementId($storeId);
        return $this->incrementValue;
    }
}
