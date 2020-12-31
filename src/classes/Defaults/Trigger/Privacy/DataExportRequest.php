<?php
/**
 * Privacy ereased trigger
 *
 * @package notification
 */

namespace BracketSpace\Notification\Defaults\Trigger\Privacy;

/**
 * Data export request trigger class
 */
class DataExportRequest extends PrivacyTrigger {

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct( 'privacy/data-export-request', __( 'Privacy Data Export Request', 'notification' ) );

		$this->add_action( 'user_request_action_confirmed', 10, 1 );

		$this->set_description( __( 'Fires when user requests privacy data export', 'notification' ) );

	}

	/**
	 * Assigns action callback args to object
	 *
	 * @param integer $request_id Request id.
	 */
	public function action( $request_id ) {

		$this->request             = wp_get_user_request( $request_id );
		$this->user_object         = get_userdata( $this->request->user_id );
		$this->data_operation_time = $this->cache( 'timestamp', time() );

	}

}
