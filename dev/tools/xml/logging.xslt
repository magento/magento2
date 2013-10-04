<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output indent="yes"/>
    <xsl:template match="*/text()[normalize-space()]">
        <xsl:value-of select="normalize-space()"/>
    </xsl:template>
    <xsl:template match="*/text()[not(normalize-space())]" />

    <xsl:template match="/">
        <logging xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:noNamespaceSchemaLocation="../../../Magento/Logging/etc/logging.xsd">
            <xsl:apply-templates select="/logging/actions"/>
            <groups>
                <xsl:apply-templates select="/logging/*[not(name()='actions')]"/>
            </groups>
        </logging>
    </xsl:template>

    <xsl:template match="/logging/actions">
        <actions>
            <xsl:apply-templates/>
        </actions>
    </xsl:template>

    <xsl:template match="/logging/actions/*" priority="1">
        <action>
            <xsl:attribute name="id">
                <xsl:value-of select="local-name()"/>
            </xsl:attribute>
            <xsl:apply-templates select="label"/>
        </action>
    </xsl:template>

    <xsl:template match="/logging/*[not(name()='actions')]">
        <group>
            <xsl:attribute name="name">
                <xsl:value-of select="local-name()"/>
            </xsl:attribute>
            <xsl:apply-templates select="label|expected_models|actions"/>
        </group>
    </xsl:template>

    <xsl:template match="label">
        <xsl:copy>
            <xsl:attribute name="translate">true</xsl:attribute>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="expected_models">
        <expected_models>
            <xsl:if test="@extends='merge'">
                <xsl:attribute name="merge_group">
                    <xsl:value-of select="'true'" />
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </expected_models>
    </xsl:template>

    <xsl:template match="expected_models/*">
        <expected_model>
            <xsl:attribute name="class">
                <xsl:value-of select="local-name()" />
            </xsl:attribute>
            <xsl:if test="./skip_data">
                <skip_fields>
                    <xsl:apply-templates/>
                </skip_fields>
            </xsl:if>
            <xsl:if test="./additional_data">
                <additional_fields>
                    <xsl:apply-templates/>
                </additional_fields>
            </xsl:if>
        </expected_model>
    </xsl:template>

    <xsl:template match="skip_on_back">
        <skip_on_back>
            <xsl:apply-templates/>
        </skip_on_back>
    </xsl:template>

    <xsl:template match="additional_data/*">
        <field>
            <xsl:attribute name="name">
                <xsl:value-of select="local-name()" />
            </xsl:attribute>
        </field>
    </xsl:template>

    <xsl:template match="skip_data/*">
        <field>
            <xsl:attribute name="name">
                <xsl:value-of select="local-name()" />
            </xsl:attribute>
        </field>
    </xsl:template>

    <xsl:template match="skip_on_back/*">
        <controller_action>
            <xsl:attribute name="name">
                <xsl:value-of select="local-name()" />
            </xsl:attribute>
        </controller_action>
    </xsl:template>

    <xsl:template match="actions">
        <events>
            <xsl:apply-templates/>
        </events>
    </xsl:template>

    <xsl:template match="actions/*">
        <event>
            <xsl:attribute name="controller_action">
                <xsl:value-of select="local-name()" />
            </xsl:attribute>
            <xsl:if test="./action">
                <xsl:attribute name="action_alias">
                    <xsl:value-of select="./action/text()" />
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="./post_dispatch">
                <xsl:attribute name="post_dispatch">
                    <xsl:value-of select="./post_dispatch/text()" />
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates select="expected_models"/>
            <xsl:apply-templates select="skip_on_back"/>
        </event>
    </xsl:template>
</xsl:stylesheet>