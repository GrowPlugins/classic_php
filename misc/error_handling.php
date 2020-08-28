<?php

namespace ClassicPHP {

    class ErrorHandling {

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
        public function throw_error(
            $error_description,
            $error_level = 'warning',
            $variables = [],
            $echo = false,
            $output_pre_wrapper = false ) {

            /* Declaration ***********************************************/
            $backtrace =
                debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 20 );
            $vardump = '';
            $error_message = '';
            $error_type;
            $backtrace_index = 1;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force $error_description As String */
            $error_description = strval( $error_description );

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

            /* Verify $echo is Bool */
            if ( true !== $echo ) {

                $echo = false;
            }

            /* Verify $output_pre_wrapper is Bool */
            if ( true !== $output_pre_wrapper ) {

                $output_pre_wrapper = false;
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

            foreach( $backtrace as $trace ) {

                $error_message .= $backtrace_index . '. ';

                if ( isset( $trace['class'] ) ) {

                    $error_message .= $trace['class'] . '::';
                }

                $error_message .=
                    $trace['function'] . '() was called from '
                    . $trace['file'] . ' line ' . $trace['line'];

                if ( count( $backtrace ) > $backtrace_index ) {

                    $error_message .= ", after\n";
                }
                else {

                    $error_message .= ";\n";
                }



                    $backtrace_index++;
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
