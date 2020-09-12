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

    NOTE: Let the developer develop a function to use all the clause
    building functions themselves. I just need to create the clause
    building functions. Remember select clauses can have subqueries.

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
         * Creates a SELECT clause string for use within a selection
         * statement. Does not allow the use of subqueries in the clause.
         * Fields should be validated prior to using this method.
         * @param string[] $fields
         * @param mixed string[] string $functions
         * @return string[]
         * @return bool
         */
        function create_selection_clause(
            array $fields,
            $functions = [''] ) {

            /* Definition ************************************************/
            $selection_clause;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $functions */
            $functions = $this->remove_invalid_functions( $functions );

            if ( false === $functions ) {

                $functions = [''];
            }

            /* Build Clause ---------------------------------------------*/
            $selection_clause = 'SELECT ';

            foreach ( $fields as $key => $field ) {

                /* Build Fields into SELECT Clause */
                // Add Field with Valid Function
                if (
                    array_key_exists( $key, $functions )
                    && '' !== $functions[ $key ] ) {

                    $selection_clause .=
                        $functions[ $key ] . '(' . $field . '), ';
                }

                // Add Field without Function
                else {

                    $selection_clause .= $field . ', ';
                }

                /* Handle Case where '*' is Now in SELECT Clause */
                if ( '*' === $field ) {

                    if ( $key === array_key_first( $fields ) ) {

                        break;
                    }
                    else {

                        return false;
                    }
                }
            }

            // Remove Trailing ', '
            $selection_clause = substr(
                $selection_clause,
                0,
                strlen( $selection_clause ) - 2 );

            return $selection_clause;
        }

        /** @method create_from_clause
         * Creates a FROM clause string for use within a selection
         * statement. Does not allow the use of subqueries in the clause.
         * Tables and fields should be validated prior to using this
         * method.
         * @param string $table
         * @param string[] $joined_tables
         * @param string[] $join_types
         * @return string[]
         * @return bool
         */
        function create_from_clause(
            string $table,
            array $joined_tables = [],
            array $join_types = [''] ) {

            /* Definition ************************************************/
            $from_clause;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $join_types */
            if (
                $this->arrays->validate_data_types(
                    $join_types,
                    'string' ) ) {

                foreach ( $join_types as $join_type ) {



                    if ( 'LEFT' ) {

                        //
                    }
                }
            }

            /* Build Clause ---------------------------------------------*/
            $selection_clause = 'SELECT ';

            foreach ( $fields as $key => $field ) {

                /* Build Fields into SELECT Clause */
                // Add Field with Valid Function
                if (
                    array_key_exists( $key, $functions )
                    && '' !== $functions[ $key ] ) {

                    $selection_clause .= $functions[ $key ] . '(' . $field . '), ';
                }

                // Add Field without Function
                else {

                    $selection_clause .= $field . ', ';
                }

                /* Handle Case where '*' is in SELECT Clause */
                if ( '*' === $field ) {

                    if ( $key === array_key_first( $fields ) ) {

                        break;
                    }
                    else {

                        return false;
                    }
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
            $valid_functions =
                $this->read_json_file( $valid_functions_json_file, true );

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

        private function read_json_file(
            $json_file,
            $return_json_array = false ) {

            /* Definition ************************************************/
            $json_string;

            /* Read JSON Array File of Valid Functions */
            if ( file_exists( $json_file ) ) {

                ob_start();

                readfile( $json_file );

                $json_string = ob_get_clean();

                return
                    json_decode(
                        $json_string,
                        $return_json_array );
            }
        }
    }
}
