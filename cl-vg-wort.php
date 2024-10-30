<?php

declare(strict_types=1);

/**
 * VG Wort - Report texts (automatically) online
 *
 * @package   CrayssnLabs\VgWort
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright CrayssnLabs Ludwig Wiegler GbR
 * @version   1.0.4
 *
 * @wordpress-plugin
 * Plugin Name:       VG Wort - Report texts (automatically) online
 * Description:       This plugin automatically integrates the VG Wort tracking pixels and submits them for verification.
 * Version:           1.0.4
 * Requires at least: 5.2
 * Requires PHP:      8.0
 * Author:            CrayssnLabs
 * Author URI:        https://cl.team
 * Text Domain:       cl-vg-wort
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if (! defined('ABSPATH')) {
    return;
}

require_once __DIR__ . '/src/Autoloader.php';

CrayssnLabsVgWort\CrayssnLabsVgWort::init();
