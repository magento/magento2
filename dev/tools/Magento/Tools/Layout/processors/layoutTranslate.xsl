<!--
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    extension-element-prefixes="php"
    exclude-result-prefixes="xsl php">

    <xsl:output method="xml" omit-xml-declaration="yes"/>
    <xsl:variable name="schemaPath" select="'https://raw.github.com/magento/magento2/master/app/code/Mage/Core/etc/layouts.xsd'"/>

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <!-- Remove translate attribute attribute -->
    <xsl:template match="@translate[.!='true']">
        <xsl:apply-templates select="node()"/>
    </xsl:template>

    <!-- Move translate attribute to exact node -->
    <xsl:template match="*[../@translate]">
        <xsl:variable name="translate" select="../@translate"/>
        <xsl:choose>
            <xsl:when test="@name=$translate">
                <xsl:copy>
                    <xsl:attribute name="translate">true</xsl:attribute>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:copy>
            </xsl:when>
            <xsl:when test="contains($translate, ' ')">
                <xsl:call-template name="tokenize">
                    <xsl:with-param name="translate" select="$translate" />
                    <xsl:with-param name="separator" select="' '" />
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:copy>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- Subroutine for translate it it contains separator -->
    <xsl:template name="tokenize">
        <xsl:param name="translate"/>
        <xsl:param name="separator"/>
        <xsl:variable name="first-item" select="normalize-space(substring-before(concat($translate, $separator), $separator))" />
        <xsl:choose>
            <xsl:when test="$first-item=@name">
                <xsl:copy>
                    <xsl:attribute name="translate">true</xsl:attribute>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:copy>
            </xsl:when>
            <xsl:when test="$first-item">
                <xsl:call-template name="tokenize">
                    <xsl:with-param name="translate" select="substring-after($translate, $separator)" />
                    <xsl:with-param name="separator" select="$separator" />
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:copy>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
