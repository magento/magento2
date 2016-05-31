<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Api\Data;

/**
 * Admin user interface.
 *
 * @api
 */
interface UserInterface
{
    /**
     * Get ID.
     *
     * @return int
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get first name.
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Set first name.
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName);

    /**
     * Get last name.
     *
     * @return string
     */
    public function getLastName();

    /**
     * Set last name.
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName);

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email.
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get user name.
     *
     * @return string
     */
    public function getUserName();

    /**
     * Set user name.
     *
     * @param string $userName
     * @return $this
     */
    public function setUserName($userName);

    /**
     * Get password or password hash.
     *
     * @return string
     */
    public function getPassword();

    /**
     * Set password or password hash.
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password);

    /**
     * Get user record creation date.
     *
     * @return string
     */
    public function getCreated();

    /**
     * Set user record creation date.
     *
     * @param string $created
     * @return $this
     */
    public function setCreated($created);

    /**
     * Get user record modification date.
     *
     * @return string
     */
    public function getModified();

    /**
     * Set user record modification date.
     *
     * @param string $modified
     * @return $this
     */
    public function setModified($modified);

    /**
     * Check if user is active.
     *
     * @return int
     */
    public function getIsActive();

    /**
     * Set if user is active.
     *
     * @param int $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Get user interface locale.
     *
     * @return string
     */
    public function getInterfaceLocale();

    /**
     * Set user interface locale.
     *
     * @param string $interfaceLocale
     * @return $this
     */
    public function setInterfaceLocale($interfaceLocale);
}
