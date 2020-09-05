<?php

namespace ClassicPHP {

    /* Class Using Aliases */
    use \PDO as PDO;

    /* Class Includes */
    // Determine ClassicPHP Base Path
    if ( ! defined( 'CLASSIC_PHP_DIR' ) ) {

        $dir = strstr( __DIR__, 'classic_php', true ) . 'classic_php';

        define( 'CLASSIC_PHP_DIR', $dir );

        unset( $dir );
    }

    // Includes List
    require_once( __DIR__ . '/mysql_pdo.php' );
    require_once( CLASSIC_PHP_DIR . '/data_types/array_processing.php' );

    /*
        Query:
            SELECT Function(fields) AS fieldNames
            FROM table
            JOIN table
                ON field = value
            GROUP BY fields
            HAVING field = value
            WHERE field = value
            LIMIT number, number

        Update:
            UPDATE table
            SET field = value

            INSERT INTO table
            (fields)
            VALUES (values)

            DELETE table
            WHERE field = value

        Create:
            CREATE table
            (fields)
            VALUES (values)

        Drop:
            DROP table
    */

    /*

    - Query database data for PDO connections
    - Alter database data
    - Drop database data

    --Query

    A query often requires user input, so validation and escaping is necessary.

    SELECT -- Validate fields against fields from table, and '*'
    FROM -- Validate table against tables from database
    WHERE -- Validate fields against fields from table. Validate values and use PDO encapsulation.
    ORDER BY -- Validate fields against fields from table.
    LIMIT -- Validate values.

    */

    /** Class: MySQLPDO_Read
     * Allows you to query a database safely using PDO.
     * Inherits From: ClassicPHP\MySQLPDO
     * Requires: \PDO
     * Inherited By: None
     *********************************************************************/
    class MySQLPDO_Read extends MySQLPDO {

        protected $arrays;

        function __construct( PDO $pdo_connection ) {

            parent::__construct( $pdo_connection );

            $this->arrays = new ArrayProcessing();
            $this->error = new ErrorHandling();
        }

        /** @method create_selection_clause
         * Creates a selection clause string for use within a selection
         * statement.
         * @param mixed string[] string $table_names
         * @param string $return_type -- array, string, bool/boolean
         * @return string[]
         * @return string
         * @return bool
         */
        function create_selection_clause( $fields, $functions = [''] ) {

            /* Definition ************************************************/
            $selection_clause;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $fields */
            if ( ! is_array( $fields ) ) {

                return false;
            }

            /* Validate $functions */
            $functions = $this->remove_invalid_functions( $functions );

            if ( false === $functions ) {

                $functions = [''];
            }

            /* Build Clause ---------------------------------------------*/
            $selection_clause = 'SELECT ';

            foreach ( $fields as $key => $field ) {

                if (
                    array_key_exists( $key, $functions )
                    && '' !== $functions[ $key ] ) {

                    $selection_clause .= $functions[ $key ] . '(' . $field . '), ';
                }
                else {

                    $selection_clause .= $field . ', ';
                }
            }

            // Remove Trailing ', '
            $selection_clause = substr(
                $selection_clause,
                0,
                strlen( $selection_clause ) - 2 );

            return $selection_clause;
        }

        /** @method remove_invalid_functions
         * Replaces invalid functions with empty strings. If $return_type
         * is 'bool' and any function is invalid, false is returned.
         * @param mixed string[] string $functions
         * @param string $return_type -- array, bool/boolean
         * @return string[]
         * @return bool
         */
        private function remove_invalid_functions(
            $functions,
            $return_type = 'array' ) {

            /* Definition ************************************************/
            $valid_functions;
            $valid_function_found = false;

            /* JSON Data File Variables */
            $valid_functions_json_file =
                CLASSIC_PHP_DIR
                . '/classic_php_data_files/mysql_functions.json';
            $valid_functions_json_string;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force $functions to Be Array of Strings */
            // Test If Array and If Every Element is String Data Type
            if (
                ! $this->arrays->validate_data_types(
                    $functions,
                    'string' ) ) {

                // If Not, and Not Even String Then Return False
                if ( ! is_string( $functions ) ) {

                    return false;
                }

                // Else If Not Array, But is String, Make String Array
                else {

                    $functions = [ $functions ];
                }
            }

            /* Validate $return_type */
            if ( 'array' !== $return_type ) {

                $return_type = 'bool';
            }

            /* Force $functions Elements to be Uppercase for Matching */
            foreach ( $functions as $key => $function ) {

                $functions[ $key ] = strtoupper( $function );
            }

            /* Processing ************************************************/
            /* Read JSON Array File of Valid Functions */
            if ( file_exists( $valid_functions_json_file ) ) {

                ob_start();

                readfile( $valid_functions_json_file );

                $valid_functions_json_string = ob_get_clean();

                $valid_functions =
                    json_decode(
                        $valid_functions_json_string,
                        true );
            }

            /* Remove Invalid Function Names from $functions */
            foreach ( $functions as $key => $function ) {

                $valid_function_found = false;

                foreach( $valid_functions as $valid_function ) {

                    if ( $valid_function === $function ) {

                        $valid_function_found = true;
                        break;
                    }
                }

                if (
                    ! $valid_function_found
                    && 'bool' === $return_type ) {

                    return false;
                }
                elseif ( ! $valid_function_found ) {

                    $functions[ $key ] = '';
                }
            }

            /* Return ****************************************************/
            return $functions;
        }
    }
}
