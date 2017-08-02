<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Api\Data;

/**
 * Admin user interface.
 *
 * @api
 * @since 2.0.0
 */
interface UserInterface
{
    /**
     * Get ID.
     *
     * @return int
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Get first name.
     *
     * @return string
     * @since 2.0.0
     */
    public function getFirstName();

    /**
     * Set first name.
     *
     * @param string $firstName
     * @return $this
     * @since 2.0.0
     */
    public function setFirstName($firstName);

    /**
     * Get last name.
     *
     * @return string
     * @since 2.0.0
     */
    public function getLastName();

    /**
     * Set last name.
     *
     * @param string $lastName
     * @return $this
     * @since 2.0.0
     */
    public function setLastName($lastName);

    /**
     * Get email.
     *
     * @return string
     * @since 2.0.0
     */
    public function getEmail();

    /**
     * Set email.
     *
     * @param string $email
     * @return $this
     * @since 2.0.0
     */
    public function setEmail($email);

    /**
     * Get user name.
     *
     * @return string
     * @since 2.0.0
     */
    public function getUserName();

    /**
     * Set user name.
     *
     * @param string $userName
     * @return $this
     * @since 2.0.0
     */
    public function setUserName($userName);

    /**
     * Get password or password hash.
     *
     * @return string
     * @since 2.0.0
     */
    public function getPassword();

    /**
     * Set password or password hash.
     *
     * @param string $password
     * @return $this
     * @since 2.0.0
     */
    public function setPassword($password);

    /**
     * Get user record creation date.
     *
     * @return string
     * @since 2.0.0
     */
    public function getCreated();

    /**
     * Set user record creation date.
     *
     * @param string $created
     * @return $this
     * @since 2.0.0
     */
    public function setCreated($created);

    /**
     * Get user record modification date.
     *
     * @return string
     * @since 2.0.0
     */
    public function getModified();

    /**
     * Set user record modification date.
     *
     * @param string $modified
     * @return $this
     * @since 2.0.0
     */
    public function setModified($modified);

    /**
     * Check if user is active.
     *
     * @return int
     * @since 2.0.0
     */
    public function getIsActive();

    /**
     * Set if user is active.
     *
     * @param int $isActive
     * @return $this
     * @since 2.0.0
     */
    public function setIsActive($isActive);

    /**
     * Get user interface locale.
     *
     * @return string
     * @since 2.0.0
     */
    public function getInterfaceLocale();

    /**
     * Set user interface locale.
     *
     * @param string $interfaceLocale
     * @return $this
     * @since 2.0.0
     */
    public function setInterfaceLocale($interfaceLocale);
}
