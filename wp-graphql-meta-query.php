<?php
/**
 * Plugin Name: WPGraphQL Meta Query
 * Plugin URI: https://github.com/wp-graphql/wp-graphql-meta-query
 * Description: Adds Meta Query support for the WPGraphQL plugin. Requires WPGraphQL version 0.0.23
 * or newer.
 * Author: Jason Bahl
 * Author URI: http://www.wpgraphql.com
 * Version: 0.1.1
 * Text Domain: wp-graphql-meta-query
 * Requires at least: 4.7.0
 * Tested up to: 4.7.1
 *
 * @package  WPGraphQLMetaQuery
 * @category WPGraphQL
 * @author   Jason Bahl
 * @version  0.1.1
 */

namespace WPGraphQL;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPGraphQL\Registry\TypeRegistry;

class MetaQuery {

	/**
	 * MetaQuery constructor.
	 *
	 * This hooks the plugin into the WPGraphQL Plugin
	 *
	 * @since 0.0.1
	 */
	public function __construct() {

		/**
		 * Setup plugin constants
		 *
		 * @since 0.0.1
		 */
		$this->setup_constants();

		/**
		 * Included required files
		 *
		 * @since 0.0.1
		 */
		$this->includes();

		/**
		 * Filter the query_args for the PostObjectQueryArgsType
		 *
		 * @since 0.0.1
		 */
		add_filter( 'graphql_input_fields', array( $this, 'add_input_fields' ), 10, 4 );

		/**
		 * Filter the $allowed_custom_args for the PostObjectsConnectionResolver to map the
		 * metaQuery input to WP_Query terms
		 *
		 * @since 0.0.1
		 */
		add_filter( 'graphql_map_input_fields_to_wp_query', array( $this, 'map_input_fields' ), 10, 2 );

	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since  0.0.1
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'WPGRAPHQL_METAQUERY_VERSION' ) ) {
			define( 'WPGRAPHQL_METAQUERY_VERSION', '0.1.1' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'WPGRAPHQL_METAQUERY_PLUGIN_DIR' ) ) {
			define( 'WPGRAPHQL_METAQUERY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'WPGRAPHQL_METAQUERY_PLUGIN_URL' ) ) {
			define( 'WPGRAPHQL_METAQUERY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'WPGRAPHQL_METAQUERY_PLUGIN_FILE' ) ) {
			define( 'WPGRAPHQL_METAQUERY_PLUGIN_FILE', __FILE__ );
		}

		// Whether to autoload the files or not
		if ( ! defined( 'WPGRAPHQL_METAQUERY_AUTOLOAD' ) ) {
			define( 'WPGRAPHQL_METAQUERY_AUTOLOAD', true );
		}

	}

	/**
	 * Include required files.
	 *
	 * Uses composer's autoload
	 *
	 * @access private
	 * @since  0.0.1
	 * @return void
	 */
	private function includes() {

		// Autoload Required Classes
		if ( defined( 'WPGRAPHQL_METAQUERY_AUTOLOAD' ) && true == WPGRAPHQL_METAQUERY_AUTOLOAD ) {
			require_once( WPGRAPHQL_METAQUERY_PLUGIN_DIR . 'vendor/autoload.php' );
		}

	}

	/**
	 * add_input_fields
	 *
	 * This adds the metaQuery input fields
	 *
	 * @param array        $fields
	 * @param string       $type_name
	 * @param array        $config
	 * @param TypeRegistry $type_registry
	 *
	 * @return mixed
	 * @since 0.0.1
	 * @throws \Exception
	 */
	public function add_input_fields( $fields, $type_name, $config, $type_registry ) {
		if ( isset( $config['queryClass'] ) && 'WP_Query' === $config['queryClass'] ) {
			$this->register_types( $type_name, $type_registry );
			$fields['metaQuery'] = array(
				'type' => $type_name . 'MetaQuery',
			);
		}

		return $fields;
	}

	/**
	 * @param              $type_name
	 * @param TypeRegistry $type_registry
	 *
	 * @throws \Exception
	 */
	public function register_types( $type_name, TypeRegistry $type_registry ) {

		$type_registry->register_enum_type(
			$type_name . 'MetaTypeEnum',
			array(
				'values' => array(
					'NUMERIC'  => array(
						'name'  => 'NUMERIC',
						'value' => 'NUMERIC',
					),
					'BINARY'   => array(
						'name'  => 'BINARY',
						'value' => 'BINARY',
					),
					'CHAR'     => array(
						'name'  => 'CHAR',
						'value' => 'CHAR',
					),
					'DATE'     => array(
						'name'  => 'DATE',
						'value' => 'DATE',
					),
					'DATETIME' => array(
						'name'  => 'DATETIME',
						'value' => 'DATETIME',
					),
					'DECIMAL'  => array(
						'name'  => 'DECIMAL',
						'value' => 'DECIMAL',
					),
					'SIGNED'   => array(
						'name'  => 'SIGNED',
						'value' => 'SIGNED',
					),
					'TIME'     => array(
						'name'  => 'TIME',
						'value' => 'TIME',
					),
					'UNSIGNED' => array(
						'name'  => 'UNSIGNED',
						'value' => 'UNSIGNED',
					),
				),
			)
		);

		$type_registry->register_enum_type(
			$type_name . 'MetaCompareEnum',
			array(
				'values' => array(
					'EQUAL_TO'                 => array(
						'name'  => 'EQUAL_TO',
						'value' => '=',
					),
					'NOT_EQUAL_TO'             => array(
						'name'  => 'NOT_EQUAL_TO',
						'value' => '!=',
					),
					'GREATER_THAN'             => array(
						'name'  => 'GREATER_THAN',
						'value' => '>',
					),
					'GREATER_THAN_OR_EQUAL_TO' => array(
						'name'  => 'GREATER_THAN_OR_EQUAL_TO',
						'value' => '>=',
					),
					'LESS_THAN'                => array(
						'name'  => 'LESS_THAN',
						'value' => '<',
					),
					'LESS_THAN_OR_EQUAL_TO'    => array(
						'name'  => 'LESS_THAN_OR_EQUAL_TO',
						'value' => '<=',
					),
					'LIKE'                     => array(
						'name'  => 'LIKE',
						'value' => 'LIKE',
					),
					'NOT_LIKE'                 => array(
						'name'  => 'NOT_LIKE',
						'value' => 'NOT LIKE',
					),
					'IN'                       => array(
						'name'  => 'IN',
						'value' => 'IN',
					),
					'NOT_IN'                   => array(
						'name'  => 'NOT_IN',
						'value' => 'NOT IN',
					),
					'BETWEEN'                  => array(
						'name'  => 'BETWEEN',
						'value' => 'BETWEEN',
					),
					'NOT_BETWEEN'              => array(
						'name'  => 'NOT_BETWEEN',
						'value' => 'NOT BETWEEN',
					),
					'EXISTS'                   => array(
						'name'  => 'EXISTS',
						'value' => 'EXISTS',
					),
					'NOT_EXISTS'               => array(
						'name'  => 'NOT_EXISTS',
						'value' => 'NOT EXISTS',
					),
				),
			)
		);

		$type_registry->register_input_type(
			$type_name . 'MetaArray',
			array(
				'fields' => array(
					'key'     => array(
						'type'        => 'String',
						'description' => __( 'Custom field key', 'wp-graphql' ),
					),
					'value'   => array(
						'type'        => 'String',
						'description' => __( 'Custom field value', 'wp-graphql' ),
					),
					'values'  => array(
						'type'        => array( 'list_of' => 'String' ),
						'description' => __( 'Custom field values', 'wp-graphql' ),
					),
					'compare' => array(
						'type'        => $type_name . 'MetaCompareEnum',
						'description' => __( 'Custom field value', 'wp-graphql' ),
					),
					'type'    => array(
						'type'        => $type_name . 'MetaTypeEnum',
						'description' => __( 'Custom field value', 'wp-graphql' ),
					),
				),
			)
		);

		$type_registry->register_input_type(
			$type_name . 'MetaQuery',
			array(
				'fields' => array(
					'relation'  => array(
						'type' => 'RelationEnum',
					),
					'metaArray' => array(
						'type' => array(
							'list_of' => $type_name . 'MetaArray',
						),
					),
				),
			)
		);

	}

	/**
	 * map_input_fields
	 *
	 * This maps the metaQuery input fields to the WP_Query
	 *
	 * @param $query_args
	 * @param $input_args
	 *
	 * @return mixed
	 * @since 0.0.1
	 */
	public function map_input_fields( $query_args, $input_args ) {

		/**
		 * check to see if the metaQuery came through with the input $args, and
		 * map it properly to the $queryArgs that are returned and passed to the WP_Query
		 *
		 * @since 0.0.1
		 */
		$meta_query = null;
		if ( ! empty( $input_args['metaQuery'] ) ) {
			$meta_query = $input_args['metaQuery'];
			if ( ! empty( $meta_query['metaArray'] ) && is_array( $meta_query['metaArray'] ) ) {
				if ( 2 < count( $meta_query['metaArray'] ) ) {
					unset( $meta_query['relation'] );
				}
				foreach ( $meta_query['metaArray'] as $idx => $value ) {
					$meta_query[] = array(
						// $idx => $value,
						$idx => array(
							'key'     => $value['key'],
							'compare' => $value['compare'],
							'type'    => $value['type'],
							'value'   => $value['values'] ?? $value['value'],
						),
					);
				}
			}
			unset( $meta_query['metaArray'] );

		}
		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		/**
		 * Retrun the $query_args
		 *
		 * @since 0.0.1
		 */
		return $query_args;

	}

}

/**
 * Instantiate the MetaQuery class on graphql_init
 *
 * @return MetaQuery
 */
function graphql_init_meta_query() {
	return new MetaQuery();
}

add_action( 'graphql_init', '\WPGraphql\graphql_init_meta_query' );
