<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Serializer;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Serialize data to JSON, unserialize JSON encoded data
 *
 * @api
 * @since 100.2.0
 */
class Json implements SerializerInterface
{
    private $appState = NULL;

    public function __construct(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->appState = $objectManager->get('Magento\Framework\App\State');
    }
    /**
     * {@inheritDoc}
     * @since 100.2.0
     */
    public function serialize($data)
    {
        $result = json_encode($data);
        if (false === $result) {
            $errorMessage = "Unable to serialize value.";
            if(!$this->isOnProduction()){
                $errorMessage .= "Error: " . json_last_error_msg();
            }
            throw new \InvalidArgumentException($errorMessage);
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     * @since 100.2.0
     */
    public function unserialize($string)
    {
        $result = json_decode($string, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = "Unable to unserialize value.";
            if(!$this->isOnProduction()){
                $errorMessage .= "Error: " . json_last_error_msg();
            }
            throw new \InvalidArgumentException($errorMessage);
        }
        return $result;
    }
    
    private function isOnProduction(){
        return $this->appState === \Magento\Framework\App\State::MODE_PRODUCTION;
    }
}
