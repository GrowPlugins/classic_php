<?php

namespace ClassicPHP;

/**************************************************************************
 * Class Definition -------------------------------------------------------
 *************************************************************************/
if ( ! class_exists( '\ClassicPHP\ErrorHandling' ) ) {

    /** Class: ErrorHandling
     * Helps you to manage the handling of errors easier.
     * Inherits From: None
     * Requires: None
     * Inherited By: None
     */
    class ErrorHandling {

        /******************************************************************
        * Public Methods
        ******************************************************************/

        /** @method throw_error
         * Allows you to throw an error. Can optionally use "pretty" error
         * message formatting, which uses additional line breaks and puts
         * any screen output within a <pre> element for nicer display.
         * @param string $error_description
         * @param string $error_level
         * @param bool $pretty_output
         */
        function throw_error(
            string $error_description,
            string $error_level = 'warning',
            bool $pretty_output = false ) {

            /* Declaration ***********************************************/
            $backtrace =
                debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 20 );
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

            /* Build Error Message --------------------------------------*/
            $error_message = $error_description . "\nBacktrace:\n";

            $error_message .= $this->format_backtrace(
                $backtrace,
                $pretty_output );

            /* Output Error Information ---------------------------------*/
            if ( ! $pretty_output ) {

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

        /** @method throw_detailed_error
         * Allows you to throw an error with optional details about
         * any variables related to the error. Can optionally use "pretty"
         * error message formatting, which uses additional line breaks and
         * puts any screen output within a <pre> element for nicer display.
         * @param string $error_description
         * @param string $error_level
         * @param mixed[] $variables
         * @param bool $pretty_output
         */
        function throw_detailed_error(
            string $error_description,
            string $error_level = 'warning',
            $variables = [],
            bool $pretty_output = false ) {

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
            $error_message = $error_description . "\nBacktrace:\n";

            $error_message .= $this->format_backtrace(
                $backtrace,
                $pretty_output );

            /* Append Var Dump Data */
            if ( '' !== $vardump ) {

                $error_message .= "\nVariable Dump:\n" . $vardump;
            }

            /* Throw Error with Error Message ---------------------------*/
            if ( ! $pretty_output ) {

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

        /** @method echo_error
         * Allows you to echo an error instead of throwing it.
         * @param string $error_description
         * @param string $error_level
         */
        function echo_error(
            string $error_description,
            string $error_level = 'warning' ) {

            /* Declaration ***********************************************/
            $backtrace =
                debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 20 );
            $error_message = '';

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

            /* Build Error Message --------------------------------------*/
            $error_message = $error_description . "\nBacktrace:\n";

            $error_message .= $this->format_backtrace(
                $backtrace,
                true );

            /* Output Error Information ---------------------------------*/
            if ( E_USER_ERROR === $error_type ) {

                echo '<pre>';
                echo htmlentities( 'Error: ' . $error_message );
                echo '</pre>';

                exit;
            }
            elseif ( E_USER_WARNING === $error_type ) {

                echo '<pre>';
                echo htmlentities( 'Warning: ' . $error_message );
                echo '</pre>';
            }
            elseif ( E_USER_NOTICE === $error_type ) {

                echo '<pre>';
                echo htmlentities( 'Notice: ' . $error_description );
                echo '</pre>';
            }
        }

        /******************************************************************
        * Private Methods
        ******************************************************************/

        /** @method format_backtrace
         * Returns a prettified backtrace.
         * @param array $backtrace
         * @param bool $use_newlines
         * @return string $error_message
         */
        private function format_backtrace(
            array $backtrace,
            bool $use_newlines = false ) {

            /* Declaration ***********************************************/
            $error_message = '';
            $separator_character = ' ';

            /* Processing ************************************************/
            if ( $use_newlines ) {

                $separator_character = "\n";
            }

            /* Build Backtrace Message ----------------------------------*/
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

                    $error_message .=
                        " was called by" . $separator_character;
                }
                else {

                    $error_message .=
                        ", which originated from"
                        . $separator_character . ( $i + 2 ) . '. '
                        . $backtrace[ $i ]['file'] . ', line '
                        . $backtrace[ $i ]['line'] . "\n";
                }
            }

            /* Return ****************************************************/
            return $error_message;
        }
    }
}
