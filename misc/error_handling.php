<?php

namespace ClassicPHP {

    class ErrorHandling {

        public function throw_error(
            $error_description,
            $variables = [],
            $error_level = 'warning',
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
            /* Validate $error_level */
            if ( 'warning' === $error_level ) {

                $error_type = E_USER_WARNING;
            }
            elseif ( 'notice' === $error_level ) {

                $error_type = E_USER_NOTICE;
            }
            else {

                $error_type = E_USER_ERROR;
            }

            /* Validate $echo */
            if ( true !== $echo ) {

                $echo = false;
            }

            /* Validate $output_pre_wrapper */
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
                        htmlentities( $error_message ) , $error_type );
                }
                else {
                    echo '<pre>';
                    trigger_error(
                        htmlentities( $error_message ) , $error_type );
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
