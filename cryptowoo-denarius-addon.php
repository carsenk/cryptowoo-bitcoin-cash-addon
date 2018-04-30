<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Plugin Name: CryptoWoo Denarius Add-on
 * Plugin URI: https://github.com/carsenk/cryptowoo-denarius-addon
 * GitHub Plugin URI: carsenk/cryptowoo-denarius-addon
 * Forked From: CryptoWoo/cryptowoo-dash-addon, Author: flxstn
 * Description: Accept DNR payments in WooCommerce. Requires CryptoWoo main plugin and CryptoWoo HD Wallet Add-on.
 * Version: 1.0
 * Author: Olav Småriset, Modified by Carsen Klock for Denarius
 * Author URI: https://github.com/carsenk
 * License: GPLv2
 * Text Domain: cryptowoo-dnr-addon
 * Domain Path: /lang
 * Tested up to: 4.9.1
 * WC tested up to: 3.2.6
 *
 */

define( 'CWDNR_VER', '1.3' );
define( 'CWDNR_FILE', __FILE__ );
$cw_dir = WP_PLUGIN_DIR . "/cryptowoo";
$cw_license_path = "$cw_dir/am-license-menu.php";

// Load the plugin update library if it is not already loaded
if ( ! class_exists( 'CWDNR_License_Menu' ) && file_exists( $cw_license_path ) ) {
	require_once( $cw_license_path );

	class CWDNR_License_Menu extends CW_License_Menu {};

	CWDNR_License_Menu::instance( CWDNR_FILE, 'CryptoWoo Denarius Add-on', CWDNR_VER, 'plugin', 'https://www.cryptowoo.com/' );
}

/**
 * Plugin activation
 */
function cryptowoo_dnr_addon_activate() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	$hd_add_on_file = 'cryptowoo-hd-wallet-addon/cryptowoo-hd-wallet-addon.php';
	if ( ! file_exists( WP_PLUGIN_DIR . '/' . $hd_add_on_file ) || ! file_exists( WP_PLUGIN_DIR . '/cryptowoo/cryptowoo.php' ) ) {

		// If WooCommerce is not installed then show installation notice
		add_action( 'admin_notices', 'cryptowoo_dnr_notinstalled_notice' );

		return;
	} elseif ( ! is_plugin_active( $hd_add_on_file ) ) {
		add_action( 'admin_notices', 'cryptowoo_dnr_inactive_notice' );

		return;
	}
}

register_activation_hook( __FILE__, 'cryptowoo_dnr_addon_activate' );
add_action( 'admin_init', 'cryptowoo_dnr_addon_activate' );

/**
 * CryptoWoo inactive notice
 */
function cryptowoo_dnr_inactive_notice() {

	?>
    <div class="error">
        <p><?php _e( '<b>CryptoWoo Denarius add-on error!</b><br>It seems like the CryptoWoo HD Wallet add-on has been deactivated.<br>
       				Please go to the Plugins menu and make sure that the CryptoWoo HD Wallet add-on is activated.', 'cryptowoo-dnr-addon' ); ?></p>
    </div>
	<?php
}


/**
 * CryptoWoo HD Wallet add-on not installed notice
 */
function cryptowoo_dnr_notinstalled_notice() {
	$addon_link = '<a href="https://www.cryptowoo.com/shop/cryptowoo-hd-wallet-addon/" target="_blank">CryptoWoo HD Wallet add-on</a>';
	?>
    <div class="error">
        <p><?php printf( __( '<b>CryptoWoo Denarius add-on error!</b><br>It seems like the CryptoWoo HD Wallet add-on is not installed.<br>
					The CryptoWoo Denarius add-on will only work in combination with the CryptoWoo main plugin and the %s.', 'cryptowoo-dnr-addon' ), $addon_link ); ?></p>
    </div>
	<?php
}

function cwdnr_hd_enabled() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	return is_plugin_active( 'cryptowoo-hd-wallet-addon/cryptowoo-hd-wallet-addon.php' ) && is_plugin_active( 'cryptowoo/cryptowoo.php' );
}

if ( cwdnr_hd_enabled() ) {
	// Coin symbol and name
	add_filter( 'woocommerce_currencies', 'cwdnr_woocommerce_currencies', 10, 1 );
	add_filter( 'cw_get_currency_symbol', 'cwdnr_get_currency_symbol', 10, 2 );
	add_filter( 'cw_get_enabled_currencies', 'cwdnr_add_coin_identifier', 10, 1 );

	// BIP32 prefixes
	add_filter( 'address_prefixes', 'cwdnr_address_prefixes', 10, 1 );

	// Custom block explorer URL
	add_filter( 'cw_link_to_address', 'cwdnr_link_to_address', 10, 4 );

	// Options page validations
	add_filter( 'validate_custom_api_genesis', 'cwdnr_validate_custom_api_genesis', 10, 2 );
	add_filter( 'validate_custom_api_currency', 'cwdnr_validate_custom_api_currency', 10, 2 );
	add_filter( 'cryptowoo_is_ready', 'cwdnr_cryptowoo_is_ready', 10, 3 );
	add_filter( 'cw_get_shifty_coins', 'cwdnr_cw_get_shifty_coins', 10, 1 );
	add_filter( 'cw_misconfig_notice', 'cwdnr_cryptowoo_misconfig_notice', 10, 2 );

	// HD wallet management
	add_filter( 'index_key_ids', 'cwdnr_index_key_ids', 10, 1 );
	add_filter( 'mpk_key_ids', 'cwdnr_mpk_key_ids', 10, 1 );
	add_filter( 'get_mpk_data_mpk_key', 'cwdnr_get_mpk_data_mpk_key', 10, 3 );
	add_filter( 'get_mpk_data_network', 'cwdnr_get_mpk_data_network', 10, 3 );
	//ToDo: add_filter( 'cw_blockcypher_currencies', 'cwdnr_add_currency_to_array', 10, 1 );
	add_filter( 'cw_discovery_notice', 'cwdnr_add_currency_to_array', 10, 1 );

	// Currency params
	add_filter( 'cw_get_currency_params', 'cwdnr_get_currency_params', 10, 2 );

	// Order sorting and prioritizing
	add_filter( 'cw_sort_unpaid_addresses', 'cwdnr_sort_unpaid_addresses', 10, 2 );
	add_filter( 'cw_prioritize_unpaid_addresses', 'cwdnr_prioritize_unpaid_addresses', 10, 2 );
	add_filter( 'cw_filter_batch', 'cwdnr_filter_batch', 10, 2 );

	// Add discovery button to currency option
	//add_filter( 'redux/options/cryptowoo_payments/field/cryptowoo_dnr_mpk', 'hd_wallet_discovery_button' );
	add_filter( 'redux/options/cryptowoo_payments/field/cryptowoo_dnr_mpk', 'hd_wallet_discovery_button' );

	// Exchange rates
	add_filter( 'cw_force_update_exchange_rates', 'cwdnr_force_update_exchange_rates', 10, 2 );
	add_filter( 'cw_cron_update_exchange_data', 'cwdnr_cron_update_exchange_data', 10, 2 );
	add_filter( 'cw_get_bittrex_price_coin', 'cwdnr_get_bittrex_price_coin', 10, 1 );

	// Catch failing processing API (only if processing_fallback is enabled)
	add_filter( 'cw_get_tx_api_config', 'cwdnr_cw_get_tx_api_config', 10, 3 );

	// Insight API URL
	add_filter( 'cw_prepare_insight_api', 'cwdnr_override_insight_url', 10, 4 );

	// Add block explorer processing
	add_filter( 'cw_update_tx_details', 'cwdnr_cw_update_tx_details', 10, 5 );

	// Wallet config
	add_filter( 'wallet_config', 'cwdnr_wallet_config', 10, 3 );
	add_filter( 'cw_get_processing_config', 'cwdnr_processing_config', 10, 3 );

	// Options page
	add_action( 'plugins_loaded', 'cwdnr_add_fields', 10 );


}

/**
 * Denarius font color for aw-cryptocoins
 * see cryptowoo/assets/fonts/aw-cryptocoins/cryptocoins-colors.css
 */
function cwdnr_coin_icon_color() { ?>
    <style type="text/css">
        i.cc.DNR:before, i.cc.DNR-alt:before {
            content: "\e9a6";
        }

        i.cc.DNR, i.cc.DNR-alt {
            color: #cccccc;
        }
    </style>K
<?php }

add_action( 'wp_head', 'cwdnr_coin_icon_color' );

/**
 * Processing API configuration error
 *
 * @param $enabled
 * @param $options
 *
 * @return mixed
 */
function cwdnr_cryptowoo_misconfig_notice( $enabled, $options ) {
	$enabled['DNR'] = $options['processing_api_dnr'] === 'disabled' && ( (bool) CW_Validate::check_if_unset( 'cryptowoo_dnr_mpk', $options ) );

	return $enabled;
}

/**
 * Add currency name
 *
 * @param $currencies
 *
 * @return mixed
 */
function cwdnr_woocommerce_currencies( $currencies ) {
	$currencies['DNR'] = __( 'Denarius', 'cryptowoo' );

	return $currencies;
}


/**
 * Add currency symbol
 *
 * @param $currency_symbol
 * @param $currency
 *
 * @return string
 */
function cwdnr_get_currency_symbol( $currency_symbol, $currency ) {
	return $currency === 'DNR' ? 'DNR' : $currency_symbol;
}


/**
 * Add coin identifier
 *
 * @param $coin_identifiers
 *
 * @return array
 */
function cwdnr_add_coin_identifier( $coin_identifiers ) {
	$coin_identifiers['DNR'] = 'dnr';

	return $coin_identifiers;
}


/**
 * Add address prefix
 *
 * @param $prefixes
 *
 * @return array
 */
function cwdnr_address_prefixes( $prefixes ) {
	$prefixes['DNR']          = '30';
	$prefixes['DNR_MULTISIG'] = '90';

	return $prefixes;
}


/**
 * Add wallet config
 *
 * @param $wallet_config
 * @param $currency
 * @param $options
 *
 * @return array
 */
function cwdnr_wallet_config( $wallet_config, $currency, $options ) {
	if ( $currency === 'DNR' ) {
		$wallet_config                       = array(
			'coin_client'   => 'denarius',
			'request_coin'  => 'DNR',
			'multiplier'    => (float) $options['multiplier_dnr'],
			'safe_address'  => false,
			'decimals'      => 8,
			'mpk_key'       => 'cryptowoo_dnr_mpk',
			'fwd_addr_key'  => 'safe_dnr_address',
			'threshold_key' => 'forwarding_threshold_dnr'
		);
		$wallet_config['hdwallet']           = CW_Validate::check_if_unset( $wallet_config['mpk_key'], $options, false );
		$wallet_config['coin_protocols'][]   = 'dnr';
		$wallet_config['forwarding_enabled'] = false;
	}

	return $wallet_config;
}

/**
 * Add InstantSend and "raw" zeroconf settings to processing config
 *
 * @param $pc_conf
 * @param $currency
 * @param $options
 *
 * @return array
 */
function cwdnr_processing_config( $pc_conf, $currency, $options ) {
	if ( $currency === 'DNR' ) {
		$pc_conf['instant_send']       = isset( $options['dnr_instant_send'] ) ? (bool) $options['dnr_instant_send'] : false;
		$pc_conf['instant_send_depth'] = 5; // TODO Maybe add option

		// Maybe accept "raw" zeroconf
		$pc_conf['min_confidence'] = isset( $options['cryptowoo_dnr_min_conf'] ) && (int) $options['cryptowoo_dnr_min_conf'] === 0 && isset( $options['dnr_raw_zeroconf'] ) && (bool) $options['dnr_raw_zeroconf'] ? 0 : 1;
	}

	return $pc_conf;
}


/**
 * Override links to payment addresses
 *
 * @param $url
 * @param $address
 * @param $currency
 * @param $options
 *
 * @return string
 */
function cwdnr_link_to_address( $url, $address, $currency, $options ) {
	if ( $currency === 'DNR' ) {
		$url = "https://denariusexplorer.org/address/{$address}";
		if ( $options['preferred_block_explorer_dnr'] === 'custom' && isset( $options['custom_block_explorer_dnr'] ) ) {
			$url = preg_replace( '/{{ADDRESS}}/', $address, $options['custom_block_explorer_dnr'] );
			if ( ! wp_http_validate_url( $url ) ) {
				$url = '#';
			}
		}
	}

	return $url;
}

/**
 * Do blockdozer insight api processing if chosen
 *
 * @param $batch_data
 * @param $batch_currency
 * @param $orders
 * @param $processing
 * @param $options
 *
 * @return string
 */
function cwdnr_cw_update_tx_details( $batch_data, $batch_currency, $orders, $processing, $options ) {
	if ( $batch_currency == "DNR" ) {
		if ( $options['processing_api_dnr'] == "denariusexplorer" ) {
			$options['custom_api_dnr'] = "https://denariusexplorer.org/api";
		} else if ( $options['processing_api_dnr'] == "blockdozer" ) { // Needs additional
			$options['custom_api_dnr'] = "http://denariusexplorer.org/api/";
		}

		$batch = [];
		foreach ( $orders as $order ) {
			$batch[] = $order->address;
		}

		$batch_data[ $batch_currency ] = CW_Insight::insight_batch_tx_update( "DNR", $batch, $orders, $options );
		usleep( 333333 ); // Max ~3 requests/second TODO remove when we have proper rate limiting
	}

	return $batch_data;
}


/**
 * Override genesis block
 *
 * @param $genesis
 * @param $field_id
 *
 * @return string
 */
function cwdnr_validate_custom_api_genesis( $genesis, $field_id ) {
	if ( in_array( $field_id, array( 'custom_api_dnr', 'processing_fallback_url_dnr' ) ) ) {
		$genesis = '00000d5dbbda01621cfc16bbc1f9bf3264d641a5dbf0de89fd0182c2c4828fcd';
		//$genesis  = '00000000839a8e6886ab5951d76f411475428afc90947ee320161bbf18eb6048'; // 1
	}

	return $genesis;
}


/**
 * Override custom API currency
 *
 * @param $currency
 * @param $field_id
 *
 * @return string
 */
function cwdnr_validate_custom_api_currency( $currency, $field_id ) {
	if ( in_array( $field_id, array( 'custom_api_dnr', 'processing_fallback_url_dnr' ) ) ) {
		$currency = 'DNR';
	}

	return $currency;
}


/**
 * Add currency to cryptowoo_is_ready
 *
 * @param $enabled
 * @param $options
 * @param $changed_values
 *
 * @return array
 */
function cwdnr_cryptowoo_is_ready( $enabled, $options, $changed_values ) {
	$enabled['DNR']           = (bool) CW_Validate::check_if_unset( 'cryptowoo_dnr_mpk', $options, false );
	$enabled['DNR_transient'] = (bool) CW_Validate::check_if_unset( 'cryptowoo_dnr_mpk', $changed_values, false );

	return $enabled;
}


/**
 * Add currency to is_cryptostore check
 *
 * @param $cryptostore
 * @param $woocommerce_currency
 *
 * @return bool
 */
function cwdnr_is_cryptostore( $cryptostore, $woocommerce_currency ) {
	return (bool) $cryptostore ?: $woocommerce_currency === 'DNR';
}

add_filter( 'is_cryptostore', 'cwdnr_is_cryptostore', 10, 2 );

/**
 * Add currency to Shifty button option field
 *
 * @param $select
 *
 * @return array
 */
function cwdnr_cw_get_shifty_coins( $select ) {
	$select['DNR'] = sprintf( __( 'Display only on %s payment pages', 'cryptowoo' ), 'Denarius' );

	return $select;
}


/**
 * Add HD index key id for currency
 *
 * @param $index_key_ids
 *
 * @return array
 */
function cwdnr_index_key_ids( $index_key_ids ) {
	$index_key_ids['DNR'] = 'cryptowoo_dnr_index';

	return $index_key_ids;
}


/**
 * Add HD mpk key id for currency
 *
 * @param $mpk_key_ids
 *
 * @return array
 */
function cwdnr_mpk_key_ids( $mpk_key_ids ) {
	$mpk_key_ids['DNR'] = 'cryptowoo_dnr_mpk';

	return $mpk_key_ids;
}


/**
 * Override mpk_key
 *
 * @param $mpk_key
 * @param $currency
 * @param $options
 *
 * @return string
 */
function cwdnr_get_mpk_data_mpk_key( $mpk_key, $currency, $options ) {
	if ( $currency === 'DNR' ) {
		$mpk_key = "cryptowoo_dnr_mpk";
	}

	return $mpk_key;
}


/**
 * Override mpk_data->network
 *
 * @param $mpk_data
 * @param $currency
 * @param $options
 *
 * @return object
 * @throws Exception
 */
function cwdnr_get_mpk_data_network( $mpk_data, $currency, $options ) {
	if ( $currency === 'DNR' ) {
		$mpk_data->network = BitWasp\Bitcoin\Network\NetworkFactory::bitcoin();
	}

	return $mpk_data;
}

/**
 * Add currency force exchange rate update button
 *
 * @param $results
 *
 * @return array
 */
function cwdnr_force_update_exchange_rates( $results ) {
	$results['dnr'] = CW_ExchangeRates::update_altcoin_fiat_rates( 'DNR', false, true );

	return $results;
}

/**
 * Add currency to background exchange rate update
 *
 * @param $data
 * @param $options
 *
 * @return array
 */
function cwdnr_cron_update_exchange_data( $data, $options ) {
	$dnr = CW_ExchangeRates::update_altcoin_fiat_rates( 'DNR', $options );

	// Maybe log exchange rate updates
	if ( (bool) $options['logging']['rates'] ) {
		if ( $dnr['status'] === 'not updated' || strpos( $dnr['status'], 'disabled' ) ) {
			$data['dnr'] = strpos( $dnr['status'], 'disabled' ) ? $dnr['status'] : $dnr['last_update'];
		} else {
			$data['dnr'] = $dnr;
		}
	}

	return $data;
}

/**
 * Override Bittrex coin name (DNR instead of DNR)
 *
 * @param $currency
 *
 * @return string
 */
function cwdnr_get_bittrex_price_coin( $currency ) {
	if ( $currency === 'DNR' ) {
		$currency = 'DNR';
	}

	return $currency;
}

/**
 * Add currency to currencies array
 *
 * @param $currencies
 *
 * @return array
 */
function cwdnr_add_currency_to_array( $currencies ) {
	$currencies[] = 'DNR';

	return $currencies;
}


/**
 * Override currency params in xpub validation
 *
 * @param $currency_params
 * @param $field_id
 *
 * @return object
 */
function cwdnr_get_currency_params( $currency_params, $field_id ) {
	if ( strcmp( $field_id, 'cryptowoo_dnr_mpk' ) === 0 ) {
		$currency_params                     = new stdClass();
		$currency_params->strlen             = 111;
		$currency_params->mand_mpk_prefix    = 'xpub';   // bip32.org & Electrum prefix
		$currency_params->mand_base58_prefix = '0488b21e'; // Denarius
		$currency_params->currency           = 'DNR';
		$currency_params->index_key          = 'cryptowoo_dnr_index';
	}

	return $currency_params;
}

/**
 * Add DNR addresses to sort unpaid addresses
 *
 * @param array $top_n
 * @param mixed $address
 *
 * @return array
 */
function cwdnr_sort_unpaid_addresses( $top_n, $address ) {
	if ( strcmp( $address->payment_currency, 'DNR' ) === 0 ) {
		$top_n[3]['DNR'][] = $address;
	}

	return $top_n;
}

/**
 * Add DNR addresses to prioritize unpaid addresses
 *
 * @param array $top_n
 * @param mixed $address
 *
 * @return array
 */
function cwdnr_prioritize_unpaid_addresses( $top_n, $address ) {
	if ( strcmp( $address->payment_currency, 'DNR' ) === 0 ) {
		$top_n[3][] = $address;
	}

	return $top_n;
}

/**
 * Add DNR addresses to address_batch
 *
 * @param array $address_batch
 * @param mixed $address
 *
 * @return array
 */
function cwdnr_filter_batch( $address_batch, $address ) {
	if ( strcmp( $address->payment_currency, 'DNR' ) === 0 ) {
		$address_batch['DNR'][] = $address->address;
	}

	return $address_batch;
}


/**
 * Fallback on failing API
 *
 * @param $api_config
 * @param $currency
 *
 * @return array
 */
function cwdnr_cw_get_tx_api_config( $api_config, $currency ) {
	// ToDo: add Blockcypher
	if ( $currency === 'DNR' ) {
		if ( $api_config->tx_update_api === 'denariusexplorer' || $api_config->tx_update_api === 'blockdozer' ) {
			$api_config->tx_update_api   = 'insight';
			$api_config->skip_this_round = false;
		} else {
			$api_config->tx_update_api   = 'denariusexplorer';
			$api_config->skip_this_round = false;
		}
	}

	return $api_config;
}

/**
 * Override Insight API URL if no URL is found in the settings
 *
 * @param $insight
 * @param $endpoint
 * @param $currency
 * @param $options
 *
 * @return mixed
 */
function cwdnr_override_insight_url( $insight, $endpoint, $currency, $options ) {
	if ( $currency === 'DNR' && isset( $options['processing_fallback_url_dnr'] ) && wp_http_validate_url( $options['processing_fallback_url_dnr'] ) ) {
		$fallback_url = $options['processing_fallback_url_dnr'];
		$urls         = $endpoint ? CW_Formatting::format_insight_api_url( $fallback_url, $endpoint ) : CW_Formatting::format_insight_api_url( $fallback_url, '' );
		$insight->url = $urls['surl'];
	}

	return $insight;
}

/**
 * Add Redux options
 */
function cwdnr_add_fields() {
	$woocommerce_currency = get_option( 'woocommerce_currency' );

	/*
	 * Required confirmations
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confirmations',
		'id'         => 'cryptowoo_dnr_min_conf',
		'type'       => 'spinner',
		'title'      => sprintf( __( '%s Minimum Confirmations', 'cryptowoo' ), 'Denarius' ),
		'desc'       => sprintf( __( 'Minimum number of confirmations for <strong>%s</strong> transactions - %s Confirmation Threshold', 'cryptowoo' ), 'Denarius', 'Denarius' ),
		'default'    => 1,
		'min'        => 1,
		'step'       => 1,
		'max'        => 100,
	) );

	// ToDo: Enable raw zeroconf
	/*
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confirmations',
		'id'         => 'dnr_raw_zeroconf',
		'type'       => 'switch',
		'title'      => __( 'Denarius "Raw" Zeroconf', 'cryptowoo' ),
		'subtitle'   => __( 'Accept unconfirmed Denarius transactions as soon as they are seen on the network.', 'cryptowoo' ),
		'desc'       => sprintf( __( '%sThis practice is generally not recommended. Only enable this if you know what you are doing!%s', 'cryptowoo' ), '<strong>', '</strong>' ),
		'default'    => false,
		'required'   => array(
			//array('processing_api_dnr', '=', 'custom'),
			array( 'cryptowoo_dnr_min_conf', '=', 0 )
		),
	) );
	*/


	/*
	 * ToDo: Zeroconf order amount threshold
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-zeroconf',
		'id'         => 'cryptowoo_max_unconfirmed_dnr',
		'type'       => 'slider',
		'title'      => sprintf( __( '%s zeroconf threshold (%s)', 'cryptowoo' ), 'Denarius', $woocommerce_currency ),
		'desc'       => '',
		'required'   => array( 'cryptowoo_dnr_min_conf', '<', 1 ),
		'default'    => 100,
		'min'        => 0,
		'step'       => 10,
		'max'        => 500,
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-zeroconf',
		'id'         => 'cryptowoo_dnr_zconf_notice',
		'type'       => 'info',
		'style'      => 'info',
		'notice'     => false,
		'required'   => array( 'cryptowoo_dnr_min_conf', '>', 0 ),
		'icon'       => 'fa fa-info-circle',
		'title'      => sprintf( __( '%s Zeroconf Threshold Disabled', 'cryptowoo' ), 'Denarius' ),
		'desc'       => sprintf( __( 'This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo' ), 'Denarius' ),
	) );
	 */


	/*
	// Remove 3rd party confidence
	Redux::removeField( 'cryptowoo_payments', 'custom_api_confidence', false );

	/*
	 * Confidence warning
	 * /
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-confidence',
			'id'    => 'dnr_confidence_warning',
			'type'  => 'info',
			'title' => __('Be careful!', 'cryptowoo'),
			'style' => 'warning',
			'desc'  => __('Accepting transactions with a low confidence value increases your exposure to double-spend attacks. Only proceed if you don\'t automatically deliver your products and know what you\'re doing.', 'cryptowoo'),
			'required' => array('min_confidence_dnr', '<', 95)
	));

	/*
	 * Transaction confidence
	 * /

	Redux::setField( 'cryptowoo_payments', array(
			'section_id'        => 'processing-confidence',
			'id'      => 'min_confidence_dnr',
			'type'    => 'switch',
			'title'   => sprintf(__('%s transaction confidence (%s)', 'cryptowoo'), 'Denarius', '%'),
			//'desc'    => '',
			'required' => array('cryptowoo_dnr_min_conf', '<', 1),

	));


	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confidence',
		'id'      => 'min_confidence_dnr_notice',
		'type'    => 'info',
		'style' => 'info',
		'notice'    => false,
		'required' => array('cryptowoo_dnr_min_conf', '>', 0),
		'icon'  => 'fa fa-info-circle',
		'title'   => sprintf(__('%s "Raw" Zeroconf Disabled', 'cryptowoo'), 'Denarius'),
		'desc'    => sprintf(__('This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo'), 'Denarius'),
	));

	// Re-add 3rd party confidence
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-confidence',
		'id'       => 'custom_api_confidence',
		'type'     => 'switch',
		'title'    => __('Third Party Confidence Metrics', 'cryptowoo'),
		'subtitle' => __('Enable this to use the chain.so confidence metrics when accepting zeroconf transactions with your custom Bitcoin, Litecoin, or Dogecoin API.', 'cryptowoo'),
		'default'  => false,
	));
    */

	// Remove blockcypher token field
	Redux::removeField( 'cryptowoo_payments', 'blockcypher_token', false );

	/*
	 * Processing API
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'processing_api_dnr',
		'type'              => 'select',
		'title'             => sprintf( __( '%s Processing API', 'cryptowoo' ), 'Denarius' ),
		'subtitle'          => sprintf( __( 'Choose the API provider you want to use to look up %s payments.', 'cryptowoo' ), 'Denarius' ),
		'options'           => array(
			'denariusexplorer' => 'denariusexplorer.org',
			'blockdozer'   => 'Blockdozer.com',
			'custom'       => __( 'Custom (no testnet)', 'cryptowoo' ),
			'disabled'     => __( 'Disabled', 'cryptowoo' ),
		),
		'desc'              => '',
		'default'           => 'disabled',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_processing_api',
		'select2'           => array( 'allowClear' => false ),
	) );

	/*
	 * Processing API custom URL warning
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-api',
		'id'         => 'processing_api_dnr_info',
		'type'       => 'info',
		'style'      => 'critical',
		'icon'       => 'el el-warning-sign',
		'required'   => array(
			array( 'processing_api_dnr', 'equals', 'custom' ),
			array( 'custom_api_dnr', 'equals', '' ),
		),
		'desc'       => sprintf( __( 'Please enter a valid URL in the field below to use a custom %s processing API', 'cryptowoo' ), 'Denarius' ),
	) );

	/*
	 * Custom processing API URL
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'custom_api_dnr',
		'type'              => 'text',
		'title'             => sprintf( __( '%s Insight API URL', 'cryptowoo' ), 'Denarius' ),
		'subtitle'          => sprintf( __( 'Connect to any %sInsight API%s instance.', 'cryptowoo' ), '<a href="https://github.com/bitpay/insight-api/" title="Insight API" target="_blank">', '</a>' ),
		'desc'              => sprintf( __( 'The root URL of the API instance:%sLink to address:%shttps://cashexplorer.bitcoin.com/api/txs?address=%sRoot URL: %shttps://cashexplorer.bitcoin.com/api/%s', 'cryptowoo-dnr-addon' ), '<p>', '<code>', '</code><br>', '<code>', '</code></p>' ),
		'placeholder'       => 'https://denariusexplorer.org/api/',
		'required'          => array( 'processing_api_dnr', 'equals', 'custom' ),
		'validate_callback' => 'redux_validate_custom_api',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid DNR Insight API URL', 'cryptowoo' ),
		'default'           => '',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => __( 'Make sure the root URL of the API has a trailing slash ( / ).', 'cryptowoo' ),
		)
	) );

	// Re-add blockcypher token field
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'blockcypher_token',
		'type'              => 'text',
		'ajax_save'         => false, // Force page load when this changes
		'desc'              => sprintf( __( '%sMore info%s', 'cryptowoo' ), '<a href="http://dev.blockcypher.com/#rate-limits-and-tokens" title="BlockCypher Docs: Rate limits and tokens" target="_blank">', '</a>' ),
		'title'             => __( 'BlockCypher Token (optional)', 'cryptowoo' ),
		'subtitle'          => sprintf( __( 'Use the API token from your %sBlockCypher%s account.', 'cryptowoo' ), '<strong><a href="https://accounts.blockcypher.com/" title="BlockCypher account dnrboard" target="_blank">', '</a></strong>' ),
		'validate_callback' => 'redux_validate_token'
	) );

	// API Resource control information
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api-resources',
		'id'                => 'processing_fallback_url_dnr',
		'type'              => 'text',
		'title'             => sprintf( __( 'cashexplorer Denarius API Fallback', 'cryptowoo' ), 'Denarius' ),
		'subtitle'          => sprintf( __( 'Fallback to any %sInsight API%s instance in case the cashexplorer API fails. Retry cashexplorer upon beginning of the next hour. Leave empty to disable.', 'cryptowoo' ), '<a href="https://github.com/bitpay/insight-api/" title="Insight API" target="_blank">', '</a>' ),
		'desc'              => sprintf( __( 'The root URL of the API instance:%sLink to address:%shttp://blockdozer.com/insight-api/txs?address=XtuVUju4Baaj7YXShQu4QbLLR7X2aw9Gc8%sRoot URL: %shttp://blockdozer.com/insight-api/%s', 'cryptowoo-dnr-addon' ), '<p>', '<code>', '</code><br>', '<code>', '</code></p>' ),
		'placeholder'       => 'http://blockdozer.com/insight-api/',
		'required'          => array( 'processing_api_dnr', 'equals', 'blockcypher' ),
		'validate_callback' => 'redux_validate_custom_api',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid DNR Insight API URL', 'cryptowoo' ),
		'default'           => 'http://blockdozer.com/insight-api/',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => __( 'Make sure the root URL of the API has a trailing slash ( / ).', 'cryptowoo' ),
		)
	) );
	/*
	 * Preferred exchange rate provider
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'rates-exchange',
		'id'                => 'preferred_exchange_dnr',
		'type'              => 'select',
		'title'             => 'Denarius Exchange (DNR/BTC)',
		'subtitle'          => sprintf( __( 'Choose the exchange you prefer to use to calculate the %sDenarius to Bitcoin exchange rate%s', 'cryptowoo' ), '<strong>', '</strong>.' ),
		'desc'              => sprintf( __( 'Cross-calculated via BTC/%s', 'cryptowoo' ), $woocommerce_currency ),
		'options'           => array(
			'bittrex'    => 'Bittrex',
			'poloniex'   => 'Poloniex',
			'shapeshift' => 'ShapeShift'
		),
		'default'           => 'poloniex',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_exchange_api',
		'select2'           => array( 'allowClear' => false )
	) );

	/*
	 * Exchange rate multiplier
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'    => 'rates-multiplier',
		'id'            => 'multiplier_dnr',
		'type'          => 'slider',
		'title'         => sprintf( __( '%s exchange rate multiplier', 'cryptowoo' ), 'Denarius' ),
		'subtitle'      => sprintf( __( 'Extra multiplier to apply when calculating %s prices.', 'cryptowoo' ), 'Denarius' ),
		'desc'          => '',
		'default'       => 1,
		'min'           => .01,
		'step'          => .01,
		'max'           => 2,
		'resolution'    => 0.01,
		'validate'      => 'comma_numeric',
		'display_value' => 'text'
	) );

	/*
	 * Preferred blockexplorer
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting',
		'id'         => 'preferred_block_explorer_dnr',
		'type'       => 'select',
		'title'      => sprintf( __( '%s Block Explorer', 'cryptowoo' ), 'Denarius' ),
		'subtitle'   => sprintf( __( 'Choose the block explorer you want to use for links to the %s blockchain.', 'cryptowoo' ), 'Denarius' ),
		'desc'       => '',
		'options'    => array(
			'autoselect'   => __( 'Autoselect by processing API', 'cryptowoo' ),
			'denariusexplorer' => 'denariusexplorer.org',
			'blockdozer'   => 'blockdozer.com',
			'custom'       => __( 'Custom (enter URL below)' ),
		),
		'default'    => 'denariusexplorer',
		'select2'    => array( 'allowClear' => false )
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting',
		'id'         => 'preferred_block_explorer_dnr_info',
		'type'       => 'info',
		'style'      => 'critical',
		'icon'       => 'el el-warning-sign',
		'required'   => array(
			array( 'preferred_block_explorer_dnr', '=', 'custom' ),
			array( 'custom_block_explorer_dnr', '=', '' ),
		),
		'desc'       => sprintf( __( 'Please enter a valid URL in the field below to use a custom %s block explorer', 'cryptowoo' ), 'Denarius' ),
	) );
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'rewriting',
		'id'                => 'custom_block_explorer_dnr',
		'type'              => 'text',
		'title'             => sprintf( __( 'Custom %s Block Explorer URL', 'cryptowoo' ), 'Denarius' ),
		'subtitle'          => __( 'Link to a block explorer of your choice.', 'cryptowoo' ),
		'desc'              => sprintf( __( 'The URL to the page that displays the information for a single address.%sPlease add %s{{ADDRESS}}%s as placeholder for the cryptocurrency address in the URL.%s', 'cryptowoo' ), '<br><strong>', '<code>', '</code>', '</strong>' ),
		'placeholder'       => 'https://denariusexplorer.org/api/txs?address={$address}',
		'required'          => array( 'preferred_block_explorer_dnr', '=', 'custom' ),
		'validate_callback' => 'redux_validate_custom_blockexplorer',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid custom block explorer URL', 'cryptowoo' ),
		'default'           => '',
	) );

	/*
	 * Currency Switcher plugin decimals
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting-switcher',
		'id'         => 'decimals_DNR',
		'type'       => 'select',
		'title'      => sprintf( __( '%s amount decimals', 'cryptowoo' ), 'Denarius' ),
		'subtitle'   => '',
		'desc'       => __( 'This option overrides the decimals option of the WooCommerce Currency Switcher plugin.', 'cryptowoo' ),
		'required'   => array( 'add_currencies_to_woocs', '=', true ),
		'options'    => array(
			2 => '2',
			4 => '4',
			6 => '6',
			8 => '8'
		),
		'default'    => 4,
		'select2'    => array( 'allowClear' => false )
	) );


	// Remove Bitcoin testnet
	Redux::removeSection( 'cryptowoo_payments', 'wallets-hdwallet-testnet', false );

	/*
	 * HD wallet section start
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'wallets-hdwallet-dnr',
		'type'       => 'section',
		'title'      => __( 'Denarius', 'cryptowoo-hd-wallet-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'cc-DNR',
		//'subtitle' => __('Use the field with the correct prefix of your Litecoin MPK. The prefix depends on the wallet client you used to generate the key.', 'cryptowoo-hd-wallet-addon'),
		'indent'     => true,
	) );

	/*
	 * Extended public key
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'cryptowoo_dnr_mpk',
		'type'              => 'text',
		'ajax_save'         => false,
		'username'          => false,
		'title'             => sprintf( __( '%sprefix%s', 'cryptowoo-hd-wallet-addon' ), '<b>DNR "xpub..." ', '</b>' ),
		'desc'              => __( 'Denarius HD Wallet Extended Public Key (xpub...)', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_mpk',
		//'required' => array('cryptowoo_dnr_mpk', 'equals', ''),
		'placeholder'       => 'xpub...',
		// xpub format
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => sprintf( __( 'If you enter a used key you will have to run the address discovery process after saving this setting.%sUse a dedicated HD wallet (or at least a dedicated xpub) for your store payments to prevent address reuse.', 'cryptowoo-hd-wallet-addon' ), '<br>' ),
		)
	) );
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'derivation_path_dnr',
		'type'              => 'select',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s Derivation Path', 'cryptowoo-hd-wallet-addon' ), 'Denarius' ),
		'desc'              => __( 'Change the derivation path to match the derivation path of your wallet client.', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_derivation_path',
		'options'           => array(
			'0/' => __( 'm/0/i (e.g. Electrum Standard Wallet)', 'cryptowoo-hd-wallet-addon' ),
			'm'  => __( 'm/i (BIP44 Account)', 'cryptowoo-hd-wallet-addon' ),
		),
		'default'           => '116/',
		'select2'           => array( 'allowClear' => false )
	) );

	/*
	 * HD wallet section end
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

	// Re-add Bitcoin testnet section
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'wallets-hdwallet-testnet',
		'type'       => 'section',
		'title'      => __( 'TESTNET', 'cryptowoo-hd-wallet-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'fa fa-flask',
		'desc'       => __( 'Accept BTC testnet coins to addresses created via a "tpub..." extended public key. (testing purposes only!)<br><b>Depending on the position of the first unused address, it could take a while until your changes are saved.</b>', 'cryptowoo-hd-wallet-addon' ),
		'indent'     => true,
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'cryptowoo_btc_test_mpk',
		'type'              => 'text',
		'ajax_save'         => false,
		'username'          => false,
		'desc'              => __( 'Bitcoin TESTNET extended public key (tpub...)', 'cryptowoo-hd-wallet-addon' ),
		'title'             => __( 'Bitcoin TESTNET HD Wallet Extended Public Key', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_mpk',
		'placeholder'       => 'tpub...',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => sprintf( __( 'If you enter a used key you will have to run the address discovery process after saving this setting.%sUse a dedicated HD wallet (or at least a dedicated xpub) for your store payments to prevent address reuse.', 'cryptowoo-hd-wallet-addon' ), '<br>' ),
		)
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'derivation_path_btctest',
		'type'              => 'select',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s Derivation Path', 'cryptowoo-hd-wallet-addon' ), 'BTCTEST' ),
		'desc'              => __( 'Change the derivation path to match the derivation path of your wallet client.', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_derivation_path',
		'options'           => array(
			'0/' => __( 'm/0/i (e.g. Electrum Standard Wallet)', 'cryptowoo-hd-wallet-addon' ),
			'm'  => __( 'm/i (BIP44 Account)', 'cryptowoo-hd-wallet-addon' ),
		),
		'default'           => '0/',
		'select2'           => array( 'allowClear' => false )
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

}
