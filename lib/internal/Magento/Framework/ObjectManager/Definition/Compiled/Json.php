<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Definition\Compiled;

use Magento\Framework\Json\JsonInterface;

class Json extends \Magento\Framework\ObjectManager\Definition\Compiled
{
    /**
     * Mode name
     */
    const MODE_NAME  = 'json';

    /**
     * @var JsonInterface
     */
    private $json;

    /**
     * Unpack signature
     *
     * @param string $signature
     * @return mixed
     */
    protected function _unpack($signature)
    {
        return $this->getJson()->decode($signature);
    }

    /**
     * Get json encoder/decoder
     *
     * @return JsonInterface
     * @deprecated
     */
    private function getJson()
    {
        if ($this->json === null) {
            $this->json = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(JsonInterface::class);
        }
        return $this->json;
    }
}
