<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

/**
 * Trait for MutableCreationOptions Factories
 */
trait MutableCreationOptionsTrait
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
}
