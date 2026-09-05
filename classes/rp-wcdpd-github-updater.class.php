<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dukkan Automatic Updates From GitHub Releases
 *
 * Checks the GitHub Releases API for the latest release of this plugin and
 * registers it with the WordPress plugin updater. Updates are served from the
 * release's attached zip asset (which must contain the plugin folder at its
 * root) so WordPress can install the new version automatically.
 *
 * @class RP_WCDPD_GitHub_Updater
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author dukkan
 */
if (!class_exists('RP_WCDPD_GitHub_Updater')) {

final class RP_WCDPD_GitHub_Updater
{

    // GitHub repository coordinates
    private $repo_owner = 'jodukkan-max';
    private $repo_name  = 'dukkan-dynamic-pricing';

    // Object properties
    private $plugin_path;
    private $plugin_version;
    private $plugin_basename;
    private $plugin_slug;

    /**
     * Initialize updater for plugin
     *
     * @access public
     * @param string $plugin_path
     * @param string $plugin_version
     * @return void
     */
    public static function init($plugin_path, $plugin_version)
    {
        new self($plugin_path, $plugin_version);
    }

    /**
     * Constructor
     *
     * @access public
     * @param string $plugin_path
     * @param string $plugin_version
     * @return void
     */
    public function __construct($plugin_path, $plugin_version)
    {

        $this->plugin_path     = $plugin_path;
        $this->plugin_version  = $plugin_version;
        $this->plugin_basename = plugin_basename($plugin_path);
        $this->plugin_slug     = dirname($this->plugin_basename);

        // Register plugin with WordPress updater
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));

        // Override WordPress.org Plugin Install API
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);

        // Force-enable automatic updates for this plugin
        add_filter('auto_update_plugin', array($this, 'enable_auto_updates'), 10, 2);
    }

    /**
     * Force-enable automatic updates for this plugin
     *
     * WordPress evaluates this filter during the automatic updater run and
     * uses it to decide whether a plugin should be updated automatically.
     * Returning true for this plugin ensures updates are installed without
     * requiring the site admin to manually toggle auto-updates on.
     *
     * @access public
     * @param bool $update
     * @param object $item
     * @return bool
     */
    public function enable_auto_updates($update, $item)
    {

        // Item is the plugin data object (WordPress 5.5+)
        if (is_object($item) && isset($item->plugin)) {
            if ($item->plugin === $this->plugin_basename) {
                return true;
            }
        }
        // Legacy: item may be passed as the plugin basename string
        else if (is_string($item) && $item === $this->plugin_basename) {
            return true;
        }

        // Not our plugin - do not interfere
        return $update;
    }

    /**
     * Get GitHub Releases API url
     *
     * @access private
     * @return string
     */
    private function get_api_url()
    {
        return sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->repo_owner, $this->repo_name);
    }

    /**
     * Get latest release info from GitHub (cached for 1 hour)
     *
     * @access private
     * @return object|false
     */
    private function get_release_info()
    {

        $transient_key = 'rp_wcdpd_github_latest_release';

        // Get cached data
        $release = get_site_transient($transient_key);

        // Fetch from GitHub
        if ($release === false) {

            // Send request
            $request = wp_remote_get($this->get_api_url(), array(
                'timeout'   => 10,
                'headers'   => array(
                    'Accept'     => 'application/vnd.github.v3+json',
                    'User-Agent' => ('WordPress/' . get_bloginfo('version')),
                ),
            ));

            // Request failed
            if (is_wp_error($request) || wp_remote_retrieve_response_code($request) !== 200) {
                return false;
            }

            // Decode response
            $release = json_decode(wp_remote_retrieve_body($request));

            // Response does not look valid
            if (!is_object($release) || empty($release->tag_name)) {
                return false;
            }

            // Cache for 1 hour
            set_site_transient($transient_key, $release, HOUR_IN_SECONDS);
        }

        // Return release info
        return $release;
    }

    /**
     * Get package (zip) download url from release
     *
     * Prefers the first attached asset so that the downloaded archive contains
     * the plugin folder at its root. Falls back to the source zipball.
     *
     * @access private
     * @param object $release
     * @return string
     */
    private function get_package_url($release)
    {

        // Prefer an attached zip asset
        if (!empty($release->assets) && is_array($release->assets)) {
            foreach ($release->assets as $asset) {
                if (!empty($asset->browser_download_url)) {
                    return $asset->browser_download_url;
                }
            }
        }

        // Fallback to source zipball
        if (!empty($release->zipball_url)) {
            return $release->zipball_url;
        }

        // No package available
        return '';
    }

    /**
     * Register plugin with WordPress updater
     *
     * @access public
     * @param object $transient
     * @return object
     */
    public function check_for_update($transient)
    {

        // Transient is not object or checked versions are missing
        if (!is_object($transient) || empty($transient->checked)) {
            return $transient;
        }

        // Get latest release info
        $release = $this->get_release_info();

        // Unable to retrieve release info
        if (!$release) {
            return $transient;
        }

        // Get new version from tag name (strip leading "v")
        $new_version = ltrim((string) $release->tag_name, 'vV');

        // New version available
        if ($new_version && version_compare($this->plugin_version, $new_version, '<')) {

            // Build update object
            $transient->response[$this->plugin_basename] = (object) array(
                'slug'          => $this->plugin_slug,
                'plugin'        => $this->plugin_basename,
                'new_version'   => $new_version,
                'package'       => $this->get_package_url($release),
                'url'           => (!empty($release->html_url) ? $release->html_url : ''),
            );
        }

        // Return transient
        return $transient;
    }

    /**
     * Override WordPress.org Plugin Install API
     *
     * @access public
     * @param mixed $result
     * @param string $action
     * @param object $args
     * @return mixed
     */
    public function plugin_info($result, $action, $args)
    {

        // Check if it's a call for this plugin
        if ($action !== 'plugin_information' || empty($args->slug) || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        // Get latest release info
        $release = $this->get_release_info();

        // Unable to retrieve release info
        if (!$release) {
            return $result;
        }

        // Get new version from tag name (strip leading "v")
        $new_version = ltrim((string) $release->tag_name, 'vV');

        // Build plugin information object
        return (object) array(
            'name'          => 'WooCommerce Dynamic Pricing & Discounts',
            'slug'          => $this->plugin_slug,
            'version'       => $new_version,
            'author'        => 'dukkan',
            'author_profile'=> (!empty($release->html_url) ? $release->html_url : ''),
            'homepage'      => (!empty($release->html_url) ? $release->html_url : ''),
            'download_link' => $this->get_package_url($release),
            'sections'      => array(
                'description' => (!empty($release->body) ? $release->body : ''),
                'changelog'   => (!empty($release->body) ? $release->body : ''),
            ),
        );
    }

}

}
