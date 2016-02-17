<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Hydrator;

use Zend\ServiceManager\ServiceLocatorInterface;

class DelegatingHydrator implements HydratorInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $hydrators;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $hydrators
     */
    public function __construct(ServiceLocatorInterface $hydrators)
    {
        $this->hydrators = $hydrators;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(array $data, $object)
    {
        return $this->getHydrator($object)->hydrate($data, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function extract($object)
    {
        return $this->getHydrator($object)->extract($object);
    }

    /**
     * Gets hydrator of an object
     *
     * @param  object $object
     * @return HydratorInterface
     */
    protected function getHydrator($object)
    {
        return $this->hydrators->get(get_class($object));
    }
}
