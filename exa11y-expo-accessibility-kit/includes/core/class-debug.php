<?php
/**
 * Lightweight debug logger for EXA11Y.
 *
 * Provides the EXA11Y_Debug::log() API that several components call behind
 * class_exists('EXA11Y_Debug') guards. Messages are written to the PHP error
 * log only when WP_DEBUG is enabled, so production sites stay quiet.
 *
 * @package EXA11Y
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'EXA11Y_Debug' ) ) {

    class EXA11Y_Debug {

        /**
         * Write a message to the error log (only when WP_DEBUG is on).
         *
         * @param string $message The message to log.
         * @param string $level   Severity label (info|warn|error). Default 'info'.
         * @return void
         */
        public static function log( $message, $level = 'info' ) {
            if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
                return;
            }

            if ( is_array( $message ) || is_object( $message ) ) {
                $message = wp_json_encode( $message );
            }

            $level = strtoupper( (string) $level );

            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( sprintf( '[EXA11Y][%s] %s', $level, $message ) );
        }
    }
}
