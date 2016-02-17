<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Hydrator;

trait HydratorAwareTrait
{
    /**
     * Hydrator instance
     *
     * @var HydratorInterface
     * @access protected
     */
    protected $hydrator = null;

    /**
     * Set hydrator
     *
     * @param  HydratorInterface $hydrator
     * @return self
     * @access public
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * Retrieve hydrator
     *
     * @param void
     * @return null|HydratorInterface
     * @access public
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }
}
