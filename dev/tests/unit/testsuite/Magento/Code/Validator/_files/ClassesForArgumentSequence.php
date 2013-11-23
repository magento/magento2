<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace ArgumentSequence;

/**
 * Constructor Arguments Sequence Rules:
 * 
 * 01. Parent Required Object Arguments
 * 02. Parent Required Scalar Arguments
 * 03. Child Required Object Arguments
 * 04. Child Required Scalar Arguments
 * 05. Parent Optional Object Arguments
 * 06. Parent Optional Scalar Arguments
 * 07. Child Optional Object Arguments
 * 08. Child Optional Scalar Arguments
 * 09. Context object must go first
 * 10. Optional parameter with name data must go first among all optional scalar arguments
 */

class ContextObject implements \Magento\ObjectManager\ContextInterface
{

}

class ParentRequiredObject
{

}

class ParentOptionalObject
{

}

class ChildRequiredObject
{

}

class ChildOptionalObject
{

}

class ParentClass
{
    protected $contextObject;
    protected $parentRequiredObject;
    protected $parentRequiredScalar;
    protected $parentOptionalObject;
    protected $data;
    protected $parentOptionalScalar;

    public function __construct(
        ContextObject $contextObject,
        ParentRequiredObject $parentRequiredObject,
        array $parentRequiredScalar,
        ParentOptionalObject $parentOptionalObject = null,
        array $data = array(),
        array $parentOptionalScalar = array()
    ) {
        $this->contextObject        = $contextObject;
        $this->parentRequiredObject = $parentRequiredObject;
        $this->parentOptionalScalar = $parentRequiredScalar;
        $this->parentOptionalObject = $parentOptionalObject;
        $this->data                 = $data;
        $this->parentOptionalScalar = $parentOptionalScalar;
    }
}

class ValidChildClass extends ParentClass
{
    protected $childRequiredObject;
    protected $childRequiredScalar;
    protected $childOptionalObject;
    protected $childOptionalScalar;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextObject $contextObject,
        ParentRequiredObject $parentRequiredObject,
        array $parentRequiredScalar,
        ChildRequiredObject $childRequiredObject,
        array $childRequiredScalar,
        ParentOptionalObject $parentOptionalObject = null,
        array $data = array(),
        array $parentOptionalScalar = array(),
        ChildOptionalObject $childOptionalObject = null,
        array $childOptionalScalar = array()
    ) {
        $this->childRequiredObject = $childRequiredObject;
        $this->childRequiredScalar = $childRequiredScalar;
        $this->childOptionalObject = $childOptionalObject;
        $this->childOptionalScalar = $childOptionalScalar;

        parent::__construct(
            $contextObject,
            $parentRequiredObject,
            $parentRequiredScalar,
            $parentOptionalObject,
            $data,
            $parentOptionalScalar
        );
    }
}

//Rule 01. Parent Required Object Arguments must go first
class InvalidChildClassRule01 extends ParentClass
{
    protected $childRequiredObject;
    protected $childRequiredScalar;
    protected $childOptionalObject;
    protected $childOptionalScalar;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextObject $contextObject,
        array $parentRequiredScalar,
        ParentRequiredObject $parentRequiredObject,
        ChildRequiredObject $childRequiredObject,
        array $childRequiredScalar,
        ParentOptionalObject $parentOptionalObject = null,
        array $data = array(),
        array $parentOptionalScalar = array(),
        ChildOptionalObject $childOptionalObject = null,
        array $childOptionalScalar = array()
    ) {
        $this->childRequiredObject = $childRequiredObject;
        $this->childRequiredScalar = $childRequiredScalar;
        $this->childOptionalObject = $childOptionalObject;
        $this->childOptionalScalar = $childOptionalScalar;

        parent::__construct(
            $contextObject,
            $parentRequiredObject,
            $parentRequiredScalar,
            $parentOptionalObject,
            $data,
            $parentOptionalScalar
        );
    }
}

// Rule 02. Parent Required Scalar Arguments must go after Parent Required Object Arguments
class InvalidChildClassRule02 extends ParentClass
{
    protected $childRequiredObject;
    protected $childRequiredScalar;
    protected $childOptionalObject;
    protected $childOptionalScalar;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextObject $contextObject,
        ParentRequiredObject $parentRequiredObject,
        ChildRequiredObject $childRequiredObject,
        array $parentRequiredScalar,
        array $childRequiredScalar,
        ParentOptionalObject $parentOptionalObject = null,
        array $data = array(),
        array $parentOptionalScalar = array(),
        ChildOptionalObject $childOptionalObject = null,
        array $childOptionalScalar = array()
    ) {
        $this->childRequiredObject = $childRequiredObject;
        $this->childRequiredScalar = $childRequiredScalar;
        $this->childOptionalObject = $childOptionalObject;
        $this->childOptionalScalar = $childOptionalScalar;

        parent::__construct(
            $contextObject,
            $parentRequiredObject,
            $parentRequiredScalar,
            $parentOptionalObject,
            $data,
            $parentOptionalScalar
        );
    }
}

//Rule 03. Child Required Object Arguments must go after Parent Required Scalar Arguments
class InvalidChildClassRule03 extends ParentClass
{
    protected $childRequiredObject;
    protected $childRequiredScalar;
    protected $childOptionalObject;
    protected $childOptionalScalar;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextObject $contextObject,
        ParentRequiredObject $parentRequiredObject,
        array $parentRequiredScalar,
        array $childRequiredScalar,
        ChildRequiredObject $childRequiredObject,
        ParentOptionalObject $parentOptionalObject = null,
        array $data = array(),
        array $parentOptionalScalar = array(),
        ChildOptionalObject $childOptionalObject = null,
        array $childOptionalScalar = array()
    ) {
        $this->childRequiredObject = $childRequiredObject;
        $this->childRequiredScalar = $childRequiredScalar;
        $this->childOptionalObject = $childOptionalObject;
        $this->childOptionalScalar = $childOptionalScalar;

        parent::__construct(
            $contextObject,
            $parentRequiredObject,
            $parentRequiredScalar,
            $parentOptionalObject,
            $data,
            $parentOptionalScalar
        );
    }
}

//Rule 04. Child Required Scalar Arguments must go after Child Required Object Arguments
class InvalidChildClassRule04 extends ParentClass
{
    protected $childRequiredObject;
    protected $childRequiredScalar;
    protected $childOptionalObject;
    protected $childOptionalScalar;
    protected $argument;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextObject $contextObject,
        ParentRequiredObject $parentRequiredObject,
        array $parentRequiredScalar,
        ChildRequiredObject $childRequiredObject,
        array $childRequiredScalar,
        ChildRequiredObject $argument,
        ParentOptionalObject $parentOptionalObject = null,
        array $data = array(),
        array $parentOptionalScalar = array(),
        ChildOptionalObject $childOptionalObject = null,
        array $childOptionalScalar = array()
    ) {
        $this->childRequiredObject = $childRequiredObject;
        $this->childRequiredScalar = $childRequiredScalar;
        $this->childOptionalObject = $childOptionalObject;
        $this->childOptionalScalar = $childOptionalScalar;
        $this->argument = $argument;

        parent::__construct(
            $contextObject,
            $parentRequiredObject,
            $parentRequiredScalar,
            $parentOptionalObject,
            $data,
            $parentOptionalScalar
        );
    }
}

//Rule 05. Parent Optional Object Arguments must go after Child Required Scalar Arguments
class InvalidChildClassRule05 extends ParentClass
{
    protected $childRequiredObject;
    protected $childRequiredScalar;
    protected $childOptionalObject;
    protected $childOptionalScalar;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextObject $contextObject,
        ParentRequiredObject $parentRequiredObject,
        array $parentRequiredScalar,
        ChildRequiredObject $childRequiredObject,
        array $childRequiredScalar,
        array $parentOptionalScalar = array(),
        ParentOptionalObject $parentOptionalObject = null,
        array $data = array(),
        ChildOptionalObject $childOptionalObject = null,
        array $childOptionalScalar = array()
    ) {
        $this->childRequiredObject = $childRequiredObject;
        $this->childRequiredScalar = $childRequiredScalar;
        $this->childOptionalObject = $childOptionalObject;
        $this->childOptionalScalar = $childOptionalScalar;

        parent::__construct(
            $contextObject,
            $parentRequiredObject,
            $parentRequiredScalar,
            $parentOptionalObject,
            $data,
            $parentOptionalScalar
        );
    }
}

//Rule 06. Parent Optional Scalar Arguments must go after Parent Optional Object Arguments
class InvalidChildClassRule06 extends ParentClass
{
    protected $childRequiredObject;
    protected $childRequiredScalar;
    protected $childOptionalObject;
    protected $childOptionalScalar;
    protected $argument;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextObject $contextObject,
        ParentRequiredObject $parentRequiredObject,
        array $parentRequiredScalar,
        ChildRequiredObject $childRequiredObject,
        array $childRequiredScalar,
        ParentOptionalObject $parentOptionalObject = null,
        array $data = array(),
        array $parentOptionalScalar = array(),
        ChildOptionalObject $childOptionalObject = null,
        array $childOptionalScalar = array(),
        ChildOptionalObject $argument = null
    ) {
        $this->childRequiredObject = $childRequiredObject;
        $this->childRequiredScalar = $childRequiredScalar;
        $this->childOptionalObject = $childOptionalObject;
        $this->childOptionalScalar = $childOptionalScalar;
        $this->argument = $argument;

        parent::__construct(
            $contextObject,
            $parentRequiredObject,
            $parentRequiredScalar,
            $parentOptionalObject,
            $data,
            $parentOptionalScalar
        );
    }
}

//Rule 07. Child Optional Object Arguments must go after Parent Optional Scalar Arguments
class InvalidChildClassRule07 extends ParentClass
{
    protected $childRequiredObject;
    protected $childRequiredScalar;
    protected $childOptionalObject;
    protected $childOptionalScalar;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextObject $contextObject,
        ParentRequiredObject $parentRequiredObject,
        array $parentRequiredScalar,
        ChildRequiredObject $childRequiredObject,
        array $childRequiredScalar,
        ParentOptionalObject $parentOptionalObject = null,
        array $data = array(),
        array $parentOptionalScalar = array(),
        array $childOptionalScalar = array(),
        ChildOptionalObject $childOptionalObject = null
    ) {
        $this->childRequiredObject = $childRequiredObject;
        $this->childRequiredScalar = $childRequiredScalar;
        $this->childOptionalObject = $childOptionalObject;
        $this->childOptionalScalar = $childOptionalScalar;

        parent::__construct(
            $contextObject,
            $parentRequiredObject,
            $parentRequiredScalar,
            $parentOptionalObject,
            $data,
            $parentOptionalScalar
        );
    }
}

//Rule 08. Child Optional Scalar Arguments must go after Child Optional Object Arguments
class InvalidChildClassRule08 extends ParentClass
{
    protected $childRequiredObject;
    protected $childRequiredScalar;
    protected $childOptionalObject;
    protected $childOptionalScalar;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextObject $contextObject,
        ParentRequiredObject $parentRequiredObject,
        array $parentRequiredScalar,
        ChildRequiredObject $childRequiredObject,
        array $childRequiredScalar,
        ParentOptionalObject $parentOptionalObject = null,
        array $data = array(),
        ChildOptionalObject $childOptionalObject = null,
        array $childOptionalScalar = array(),
        array $parentOptionalScalar = array()
    ) {
        $this->childRequiredObject = $childRequiredObject;
        $this->childRequiredScalar = $childRequiredScalar;
        $this->childOptionalObject = $childOptionalObject;
        $this->childOptionalScalar = $childOptionalScalar;

        parent::__construct(
            $contextObject,
            $parentRequiredObject,
            $parentRequiredScalar,
            $parentOptionalObject,
            $data,
            $parentOptionalScalar
        );
    }
}

//Rule 09. Context object must go first
class InvalidChildClassRule09 extends ParentClass
{
    protected $childRequiredObject;
    protected $childRequiredScalar;
    protected $childOptionalObject;
    protected $childOptionalScalar;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ParentRequiredObject $parentRequiredObject,
        ContextObject $contextObject,
        array $parentRequiredScalar,
        ChildRequiredObject $childRequiredObject,
        array $childRequiredScalar,
        ParentOptionalObject $parentOptionalObject = null,
        array $data = array(),
        array $parentOptionalScalar = array(),
        ChildOptionalObject $childOptionalObject = null,
        array $childOptionalScalar = array()
    ) {
        $this->childRequiredObject = $childRequiredObject;
        $this->childRequiredScalar = $childRequiredScalar;
        $this->childOptionalObject = $childOptionalObject;
        $this->childOptionalScalar = $childOptionalScalar;

        parent::__construct(
            $contextObject,
            $parentRequiredObject,
            $parentRequiredScalar,
            $parentOptionalObject,
            $data,
            $parentOptionalScalar
        );
    }
}

// Rule 10. Optional parameter with name data must go first among all optional scalar arguments
class InvalidChildClassRule10 extends ParentClass
{
    protected $childRequiredObject;
    protected $childRequiredScalar;
    protected $childOptionalObject;
    protected $childOptionalScalar;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextObject $contextObject,
        ParentRequiredObject $parentRequiredObject,
        array $parentRequiredScalar,
        ChildRequiredObject $childRequiredObject,
        array $childRequiredScalar,
        ParentOptionalObject $parentOptionalObject = null,
        array $parentOptionalScalar = array(),
        array $data = array(),
        ChildOptionalObject $childOptionalObject = null,
        array $childOptionalScalar = array()
    ) {
        $this->childRequiredObject = $childRequiredObject;
        $this->childRequiredScalar = $childRequiredScalar;
        $this->childOptionalObject = $childOptionalObject;
        $this->childOptionalScalar = $childOptionalScalar;

        parent::__construct(
            $contextObject,
            $parentRequiredObject,
            $parentRequiredScalar,
            $parentOptionalObject,
            $data,
            $parentOptionalScalar
        );
    }
}