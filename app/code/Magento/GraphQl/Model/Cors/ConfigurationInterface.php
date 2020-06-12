<?php


namespace Magento\GraphQl\Model\Cors;


interface ConfigurationInterface
{
    public function isEnabled() : bool;

    public function getAllowedOrigins() : ?string;

    public function getAllowedHeaders() : ?string;

    public function getAllowedMethods() : ?string;

    public function getMaxAge() : int;

    public function isCredentialsAllowed() : bool;
}
