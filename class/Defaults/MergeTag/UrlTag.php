<?php
/**
 * URL merge tag class
 *
 * @package notification
 */

namespace underDEV\Notification\Defaults\MergeTag;

use underDEV\Notification\Abstracts\MergeTag;

/**
 * URL merge tag class
 */
class UrlTag extends MergeTag {

    /**
     * Check the merge tag value type
     *
     * @param  mixed $value value.
     * @return boolean
     */
    public function validate( $value ) {
    	return filter_var( $value, FILTER_VALIDATE_URL ) !== false;
    }

    /**
     * Sanitizes the merge tag value
     *
     * @param  mixed $value value.
     * @return mixed
     */
    public function sanitize( $value ) {
    	return esc_url( $value );
    }

}