<?php

/*  Copyright 2012 Rodrigo Primo  (email: rodrigo@hacklab.com.br)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Plugin Name: OpenID Delegation
Plugin URI: https://github.com/rodrigosprimo/wordpress-openid-delegation
Description: Delegates your blog URL to the OpenID provider you choose.
Version: 0.1
Author: Rodrigo Primo
Author URI: http://hacklab.com.br/
License: GPL2
*/

//TODO: internationalize the plugin

class OpenIdDelegationPlugin
{
    public function init()
    {
        add_settings_section(
            'openid_delegation_section',
            'OpenID Delegation',
            array($this, 'settingsDescription'),
            'general'
        );

        add_settings_field(
            'openid_delegation_provider',
            'OpenID Provider URL',
            array($this, 'providerField'),
            'general',
            'openid_delegation_section'
        );
        
        add_settings_field(
            'openid_delegate_url',
            'OpenID Delegate URL',
            array($this, 'delegateField'),
            'general',
            'openid_delegation_section'
        );
    
        register_setting('general', 'openid_delegation_provider');
        register_setting('general', 'openid_delegation_delegate');
    }
    
    public function settingsDescription()
    {
        echo '<p>Please enter your OpenID provider URL and your OpenID delegate URL.</p>';
    }
    
    public function providerField()
    {
        echo '<input name="openid_delegation_provider" id="openid_delegation_provider" type="text" value="' . esc_attr(get_option('openid_delegation_provider')) . '" size=20 maxlength=40 /> Example: http://www.myopenid.com/server';
    }

    public function delegateField()
    {
        echo '<input name="openid_delegation_delegate" id="openid_delegation_delegate" type="text" value="' . esc_attr(get_option('openid_delegation_delegate')) . '" size=20 maxlength=40 /> Example: http://YOURUSERNAME.myopenid.com';
    }
    
    public function renderMetaTags()
    {
        if (is_home()) {
            $provider = get_option('openid_delegation_provider');
            $delegate = get_option('openid_delegation_delegate');
            if ($provider && $delegate) {
                echo "\n<link rel='openid.server' href='$provider' />\n";
                echo "<link rel='openid.delegate' href='$delegate' />\n";
                echo "<link rel='openid2.local_id' href='$provider' />\n";
                echo "<link rel='openid2.provider' href='$delegate' />\n\n";
            }
        }
    }
}

$openIdPlugin = new OpenIdDelegationPlugin;

add_action('admin_init', array($openIdPlugin, 'init'));
add_action('wp_head', array($openIdPlugin, 'renderMetaTags'));