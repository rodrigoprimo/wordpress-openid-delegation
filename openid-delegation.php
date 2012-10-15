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

$includePath = dirname(__FILE__) . '/lib';
set_include_path($includePath . PATH_SEPARATOR . get_include_path());

require_once("Auth/OpenID/Discover.php");
require_once("Auth/Yadis/Yadis.php");

class OpenIdDelegationPlugin
{
    /**
     * Load plugin i18n files and add plugin options
     * to the Admin -> Settings -> General page.
     * 
     * @return null
     */
    public function init()
    {
        load_plugin_textdomain('openid-delegation', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
        
        add_settings_section(
            'openid_delegation_section',
            __('OpenID Delegation', 'openid-delegation'),
            array($this, 'settingsDescription'),
            'general'
        );

        add_settings_field(
            'openid_delegation_url',
            __('OpenID URL', 'openid-delegation'),
            array($this, 'openidUrlField'),
            'general',
            'openid_delegation_section'
        );
        
        register_setting('general', 'openid_delegation_url', array($this, 'validate'));
    }
    
    /**
     * Output the header of the plugin settings section.
     * 
     * @return null
     */
    public function settingsDescription()
    {
        echo '<p>' . __('Please enter your OpenID URL.', 'openid-delegation') . '</p>';
    }
    
    /**
     * Output the input field for the user OpenID URL in the plugin
     * settings section.
     * 
     * @return null
     */
    public function openidUrlField()
    {
        echo '<input name="openid_delegation_url" id="openid_delegation_url" type="text" value="' . esc_attr(get_option('openid_delegation_url')) . '" size=20 /> ' . __('Example: http://YOURUSERNAME.myopenid.com', 'openid-delegation');
    }
    
    /**
     * If there is OpenID URL in the database output to the
     * <head> section of the HTML of the home page the meta tags
     * required for OpenID delegation.
     * 
     * @return null
     */
    public function renderMetaTags()
    {
        if (is_home()) {
            $provider = get_option('openid_delegation_provider');
            $delegate = get_option('openid_delegation_url');
            if ($provider && $delegate) {
                echo "\n<link rel='openid.server' href='$provider' />\n";
                echo "<link rel='openid.delegate' href='$delegate' />\n";
                echo "<link rel='openid2.provider' href='$provider' />\n";
                echo "<link rel='openid2.local_id' href='$delegate' />\n\n";
            }
        }
    }
    
    /**
     * Check if user supplied URL is a valid OpenID and
     * get the URL provider from it.
     * 
     * @param string $identifier user supplied OpenID identifier
     * @return string
     */
    public function validate($identifier)
    {
        $oldIdentifier = get_option('openid_delegation_url');
        $fetcher = Auth_Yadis_Yadis::getHTTPFetcher();
        list($normalized_identifier, $endpoints) = Auth_OpenID_discover($identifier, $fetcher);
        
        if (!empty($identifier) && empty($endpoints)) {
            add_settings_error('openid_delegation_url', 'error', sprintf(__('No OpenID services discovered for %s.', 'openid-delegation'), $identifier));
            
            return $oldIdentifier;
        }

        update_option('openid_delegation_provider', $endpoints[0]->server_url);
        
        return $normalized_identifier;
    }
}

$openIdPlugin = new OpenIdDelegationPlugin;

add_action('admin_init', array($openIdPlugin, 'init'));
add_action('wp_head', array($openIdPlugin, 'renderMetaTags'));
