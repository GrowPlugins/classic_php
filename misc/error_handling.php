<?php

namespace ClassicPHP;

/**************************************************************************
 * Class Definition -------------------------------------------------------
 *************************************************************************/
if ( ! class_exists( '\ClassicPHP\ErrorHandling' ) ) {

    /** Class: ErrorHandling
     * Allows you to manage the handling of error easier.
     * Inherits From: None
     * Requires: None
     * Inherited By: None
     */
    class ErrorHandling {

        /******************************************************************
        * Public Methods
        ******************************************************************/

        /** @method throw_error
         * Allows you to throw an error or echo one. The error can include
         * information about variables related to the error. If error
         * handling is set to echo the error to the screen, echoing can be
         * put within a <pre> element for nicer display.
         * @param string $error_description
         * @param string $error_level
         * @param mixed[] $variables
         * @param bool $echo
         * @param bool $output_pre_wrapper
         */
        function throw_error(
            string $error_description,
            string $error_level = 'warning',
            $variables = [],
            bool $echo = false,
            bool $output_pre_wrapper = false ) {

            /* Declaration ***********************************************/
            $backtrace =
                debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 20 );
            $vardump = '';
            $error_message = '';
            $error_type;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force $error_level to be 'warning', 'notice', or 'error' */
            if ( 'warning' === $error_level ) {

                $error_type = E_USER_WARNING;
            }
            elseif ( 'notice' === $error_level ) {

                $error_type = E_USER_NOTICE;
            }
            else {

                $error_type = E_USER_ERROR;
            }

            /* Gather Information About Input Variable(s) ---------------*/
            /* Generate $vardump String If $variables Not Null Array */
            if ( [] !== $variables ) {

                ob_start();

                var_dump($variables);

                $vardump = ob_get_clean();
            }

            /* Build Error Message --------------------------------------*/
            /* Append Description */
            $error_message .= $error_description . "\n\n";

            /* Append Backtrace Data */
            $error_message .= "Backtrace:\n";

            for( $i = 0; $i < count( $backtrace ); $i++ ) {

                // Output Backtrace Index
                $error_message .= ( $i + 1) . '. ';

                // Output Class and Function in Backtrace
                if ( isset( $backtrace[ $i ]['class'] ) ) {

                    $error_message .= $backtrace[ $i ]['class'] . '::';
                }

                $error_message .=
                    $backtrace[ $i ]['function'] . '()';

                // Output Location Called From
                if ( 0 < $i ) {

                    $error_message .=
                        ' at ' . $backtrace[ $i - 1 ]['file']
                        . ', line ' . $backtrace[ $i - 1 ]['line'];
                }

                // Conditionally Output Last of Called From Sentence
                if ( $i + 1 < count( $backtrace ) ) {

                    if ( 0 < $i ) {

                        $error_message .= ', which';
                    }

                    $error_message .= " was called by\n";
                }
                else {

                    $error_message .=
                        ", which originated from\n" . ( $i + 2 ) . '. '
                        . $backtrace[ $i ]['file'] . ', line '
                        . $backtrace[ $i ]['line'] . "\n";
                }
            }

            /* Append Var Dump Data */
            if ( '' !== $vardump ) {

                $error_message .= "\nVariable Dump:\n" . $vardump;
            }

            /* Output Error Information ---------------------------------*/
            if ( false === $echo ) {

                if ( false === $output_pre_wrapper ) {

                    trigger_error(
                        htmlentities( $error_message ),
                        $error_type );
                }
                else {
                    echo '<pre>';
                    trigger_error(
                        htmlentities( $error_message ),
                        $error_type );
                    echo '</pre>';
                }
            }
            else {

                if ( false === $output_pre_wrapper ) {

                    echo $error_message;
                }
                else {
                    echo '<pre>' . $error_message . '</pre>';
                }
            }
        }
    }
}
