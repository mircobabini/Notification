<?php
/**
 * Settings Section class
 */

namespace Notification\Settings;

use Notification\Settings;

class Section {

	/**
	 * Section name
	 * @var string
	 */
	private $name;

	/**
	 * Section slug
	 * @var string
	 */
	private $slug;

	/**
	 * Section groups
	 * @var array
	 */
	private $groups = array();

	/**
	 * Section constructor
	 * @param string $name Section name
	 * @param string $slug Section slug
	 */
	public function __construct( $name, $slug ) {

		if ( empty( $name ) ) {
			throw new \Exception( 'Section name cannot be empty' );
		}

		$this->name( $name );

		if ( empty( $slug ) ) {
			throw new \Exception( 'Section slug cannot be empty' );
		}

		$this->slug( sanitize_title( $slug ) );

		Settings::get()->register_section( $this );

	}

	/**
	 * Get or set name
	 * @param  string $name Name. Do not pass anything to get current value
	 * @return string name
	 */
	public function name( $name = null ) {

		if ( $name !== null ) {
			$this->name = $name;
		}

		return apply_filters( 'notification/settings/section/name', $this->name, $this );

	}

	/**
	 * Get or set slug
	 * @param  string $slug Slug. Do not pass anything to get current value
	 * @return string slug
	 */
	public function slug( $slug = null ) {

		if ( $slug !== null ) {
			$this->slug = $slug;
		}

		return apply_filters( 'notification/settings/section/slug', $this->slug, $this );

	}

	/**
	 * Add Group to the section
	 * @param string $name Group name
	 * @param string $slug Group slug
	 * @return Group
	 */
	public function add_group( $name, $slug ) {

		if ( empty( $name ) || empty( $slug ) ) {
			throw new \Exception( 'Group name and slug cannot be empty' );
		}

		if ( isset( $this->groups[ $slug ] ) ) {
			throw new \Exception( 'Group slug `' . $slug . '` already exists' );
		}

		$this->groups[ $slug ] = new Group( $name, $slug, $this->slug() );

		do_action( 'notification/settings/group/added', $this->groups[ $slug ], $this );

		return $this->groups[ $slug ];

	}

	/**
	 * Get all registered Groups
	 * @return array
	 */
	public function get_groups() {

		return apply_filters( 'notification/settings/section/groups', $this->groups, $this );

	}

	/**
	 * Get group by group slug
	 * @param  string $slug group slug
	 * @return mixed        group object or false if no group defined
	 */
	public function get_group( $slug = '' ) {

		if ( isset( $this->groups[ $slug ] ) ) {
			return apply_filters( 'notification/settings/group', $this->group[ $slug ], $this );
		}

		return false;

	}

}