<!--
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:php="http://php.net/xsl"
    xsl:extension-element-prefixes="php"
    exclude-result-prefixes="xsl php"
    >

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*" />
        </xsl:copy>
    </xsl:template>

    <xsl:template match="action[@method='addJs' or @method='addCss']">
        <block>
            <xsl:attribute name="class">
                <xsl:choose>
                    <xsl:when test="@method = 'addJs' ">Magento\Theme\Block\Html\Head\Script</xsl:when>
                    <xsl:when test="@method = 'addCss'">Magento\Theme\Block\Html\Head\Css</xsl:when>
                </xsl:choose>
            </xsl:attribute>
            <xsl:attribute name="name">
                <xsl:value-of select="php:function('strtolower', php:function('trim', php:function('preg_replace', '/[^a-z]+/i', '-', string(./*[1])), '-'))" />
            </xsl:attribute>
            <xsl:apply-templates select="@ifconfig" />
            <arguments>
                <argument name="file" xsi:type="string">
                    <xsl:value-of select="*[1]" />
                </argument>
                <xsl:if test="count(*[position() &gt; 1])">
                    <argument name="properties" xsi:type="array">
                        <xsl:if test="*[2]"><item name="attributes" xsi:type="array"><xsl:value-of select="*[2]" /></item></xsl:if>
                        <xsl:if test="*[3]"><item name="ie_condition" xsi:type="array"><xsl:value-of select="*[3]" /></item></xsl:if>
                        <xsl:if test="*[4]"><item name="flag_name" xsi:type="array"><xsl:value-of select="*[4]" /></item></xsl:if>
                    </argument>
                </xsl:if>
            </arguments>
        </block>
    </xsl:template>

    <xsl:template match="//reference[action[@method='removeItem']]">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*" />
        </xsl:copy>
        <xsl:for-each select="action[@method='removeItem']">
            <remove name="{php:function('strtolower', php:function('trim', php:function('preg_replace', '/[^a-z]+/i', '-', string(*[2])), '-'))}" />
        </xsl:for-each>
      </xsl:template>

    <!-- Delete remove item call -->
    <xsl:template match="action[@method='removeItem']">
    </xsl:template>

</xsl:stylesheet>
