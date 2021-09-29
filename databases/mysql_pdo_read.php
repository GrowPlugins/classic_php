<?php

namespace ClassicPHP;

/**************************************************************************
 * Class Header -----------------------------------------------------------
 *************************************************************************/
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

/* Notes */
/*
    Read Queries:
        SELECT Function(fields) -->AS fieldNames
        FROM table
        JOIN table
            ON field = value -->AS tableNames
        GROUP BY fields
        HAVING field = value
        WHERE field = value
        LIMIT number, number
        ORDER BY fields
*/

/**************************************************************************
 * Class Definition -------------------------------------------------------
 *************************************************************************/
if ( ! class_exists( '\ClassicPHP\MySQLPDO_Read' ) ) {

    /** Class: MySQLPDO_Read
     * Helps you more quickly query a database safely using PDO.
     * Inherits From: ClassicPHP\MySQLPDO
     * Requires: \PDO, ClassicPHP\ArrayProcessing
     * Inherited By: None
     */
    class MySQLPDO_Read extends MySQLPDO {

        /******************************************************************
        * Public Methods
        ******************************************************************/

        /** @method __construct
         * Instantiates the super class, and the helper class
         * ArrayProcessing.
         * @param PDO $pdo_connection
         */
        function __construct( PDO $pdo_connection ) {

            parent::__construct( $pdo_connection );
        }

        /** @method build_select_clause
         * Creates a SELECT clause string for use within a selection
         * statement. Does not allow the use of subqueries in the clause.
         * Fields should be validated prior to using this method.
         * @param string[] $fields
         * @param mixed string[] string $functions
         * @return string
         */
        function build_select_clause(
            array $fields,
            $functions = [''] ) {

            /* Definition ************************************************/
            $selection_clause = '';

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $fields */
            if (
                ! $this->arrays->validate_data_types(
                    $fields,
                    'string' ) ) {

                $fields = [];
            }

            /* Validate $functions */
            $functions = $this->remove_invalid_functions( $functions );

            if ( false === $functions ) {

                $functions = [''];
            }

            /* Build Clause ---------------------------------------------*/
            $selection_clause = 'SELECT ';

            /* Process $fields If Fields Exist */
            if ( [] !== $fields ) {

                foreach ( $fields as $key => $field ) {

                    /* Build Fields into SELECT Clause */
                    // Add Field with Valid Function
                    if (
                        array_key_exists( $key, $functions )
                        && '' !== $functions[ $key ] ) {

                        $selection_clause .=
                            $functions[ $key ] . '(';

                        if ( '*' === $field ) {

                            $selection_clause .=
                                $field . '), ';
                        }
                        else {

                            $selection_clause .=
                                $this->enclose_database_object_names(
                                    $field ) . '), ';
                        }
                    }

                    // Add Field without Function
                    else {

                        if ( '*' === $field ) {

                            $selection_clause .=
                                $field . ', ';
                        }
                        else {

                            $selection_clause .=
                                $this->enclose_database_object_names(
                                    $field ) . ', ';
                        }
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
            }

            /* If No Fields, If Invalidated $fields Array, Use '*' */
            else {

                $selection_clause .= '*';
            }

            /* Return ****************************************************/
            return $selection_clause;
        }

        /** @method build_group_by_clause
         * Creates a GROUP BY clause string for use within a selection
         * statement. Fields should be validated prior to using this
         * method.
         * @param string[] $fields
         * @return string
         */
        function build_group_by_clause(
            array $fields ) {

            /* Definition ************************************************/
            $group_by_clause = '';

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $fields */
            if (
                ! $this->arrays->validate_data_types(
                    $fields,
                    'string' ) ) {

                $fields = [];
            }

            /* Build Clause ---------------------------------------------*/
            /* Process $fields If Fields Exist */
            if ( [] !== $fields ) {

                $group_by_clause = 'GROUP BY ';

                foreach ( $fields as $key => $field ) {

                    /* Build Fields into GROUP BY Clause */
                    $group_by_clause .=
                        $this->enclose_database_object_names(
                            $field ) . ', ';
                }

                // Remove Trailing ', '
                $group_by_clause = substr(
                    $group_by_clause,
                    0,
                    strlen( $group_by_clause ) - 2 );
            }

            /* Return ****************************************************/
            return $group_by_clause;
        }

        /** @method build_having_clause
         * Creates a HAVING clause string for use within a selection
         * statement. Fields should be validated prior to using this
         * method. It is highly suggested to use PDO parameter
         * placeholders (e.g., ':placeholder') for values, so you can
         * implement PDO prepared statements. However, this is not
         * required.
         * @param mixed string string[] $fields
         * @param mixed string string[] $comparison_operators
         * @param mixed $values
         * @param string[] $conditional_operators
         * @return string
         * @return false
         */
        function build_having_clause(
            $fields,
            $comparison_operators,
            $values,
            array $conditional_operators = ['AND'] ) {

            /* Definition ************************************************/
            $having_clause = '';

            /* Processing ************************************************/
            /* Build WHERE Clause, Since Having is the Same */
            $having_clause =
                $this->build_where_clause(
                    $fields,
                    $comparison_operators,
                    $values,
                    $conditional_operators );

            /* Replace WHERE with HAVING */
            $having_clause =
                str_replace( 'WHERE', 'HAVING', $having_clause );

            /* Return ****************************************************/
            return $having_clause;
        }

        /** @method build_limit_clause
         * Creates a LIMIT clause string for use within a selection
         * statement.
         * @param int $limit
         * @param int $offset
         * @return string
         */
        function build_limit_clause(
            int $limit,
            int $offset = 0 ) {

            /* Definition ************************************************/
            $limit_clause = '';

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $limit */
            if ( 0 > $limit ) {

                return '';
            }

            /* Validate $offset */
            if ( 0 > $offset ) {

                $offset = 0;
            }

            /* Build Clause ---------------------------------------------*/
            $limit_clause = 'LIMIT ';

            if ( 0 < $offset ) {

                $limit_clause .= $offset . ', ' . $limit;
            }
            else {

                $limit_clause .= $limit;
            }

            /* Return ****************************************************/
            return $limit_clause;
        }

        /******************************************************************
        * Private Methods
        ******************************************************************/

        /*-----------------------------------------------------------------
         * Class-Specific Utility Methods
         *---------------------------------------------------------------*/

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
    }
}
