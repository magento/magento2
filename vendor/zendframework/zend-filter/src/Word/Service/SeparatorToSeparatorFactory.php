<?php

namespace Zend\Filter\Word\Service;

use Zend\Filter\Word\SeparatorToSeparator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SeparatorToSeparatorFactory implements
    FactoryInterface,
    MutableCreationOptionsInterface
{
    /**
     * @var array
     */
    protected $creationOptions = array();

    /**
     * Set creation options
     *
     * @param array $creationOptions
     * @return void
     */
    public function setCreationOptions(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }

    /**
     * Get creation options
     *
     * @return array
     */
    public function getCreationOptions()
    {
        return $this->creationOptions;
    }

    /**
     * {@inheritDoc}
     *
     * @return SeparatorToSeparator
     * @throws ServiceNotCreatedException if Controllermanager service is not found in application service locator
     */
    public function createService(ServiceLocatorInterface $plugins)
    {
        return new SeparatorToSeparator(
            isset($this->creationOptions['search_separator']) ? $this->creationOptions['search_separator'] : ' ',
            isset($this->creationOptions['replacement_separator']) ? $this->creationOptions['replacement_separator'] : '-'
        );
    }
}
