<?php

namespace ClassicPHP\V0_2_0;

/**************************************************************************
 * Class Header -----------------------------------------------------------
 *************************************************************************/
/* Class Using Aliases */
use \PDO as PDO;

/* Class Includes */
// Determine ClassicPHP Base Path
if ( ! defined( 'CLASSIC_PHP_DIR' ) ) {

    $dir = strstr( __DIR__, 'classic_php', true ) . 'classic_php/V0_2_0';

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
if ( ! class_exists( '\ClassicPHP\V0_2_0\MySQLPDO_Read' ) ) {

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

        /** @method select_query
         * Returns a PDOStatement object for pulling data from the selected
         * database.
         * 
         * With $use_prepared_statements false (not recommended), the
         * select query is executed without using PDO for security. With
         * $use_prepared_statements true (highly recommended), the query
         * is executed and a PDOStatement object is returned so that the
         * same basic query can be executed afterward with different
         * values, if desired. In order to re-execute the query, call
         * execute_safe_query() with the PDOStatement object as the
         * first argument. Note that the SET or WHERE values may be
         * different, but you must have the same number of SET and/or WHERE
         * values. See:
         * https://www.php.net/manual/en/pdo.prepared-statements.php
         * 
         * Optionally return a query string instead of a
         * PDOStatement, by setting the $return_string_only argument to
         * true.
         * 
         * The existence of fields in the selected table should be
         * verified prior to using this method.
         * 
         * @param $select_fields,
         * @param string $from_table,
         *
         * @param $select_functions
         * @param bool $select_all
         * @param bool $select_distinct
         * @param bool $select_high_priority
         * @param bool $select_straight_join
         * @param $from_joined_tables
         * @param $from_join_types
         * @param $from_join_on_fields
         * @param $from_join_on_comparisons
         * @param $from_join_on_values
         * @param $where_fields
         * @param $where_comparison_operators
         * @param $where_values
         * @param $where_conditional_operators
         * @param $group_by_fields
         * @param $having_fields
         * @param $having_comparison_operators
         * @param $having_values
         * @param $having_conditional_operators
         * @param $order_by_fields
         * @param int $limit_limit
         * @param int $limit_offset
         * @param bool $return_string_only
         * @param bool $use_prepared_statements
         * @return PDOStatement|false|string
         */
        function select_query(
            $select_fields,
            string $from_table,

            $select_functions = [''],
            bool $select_all = false,
            bool $select_distinct = false,
            bool $select_high_priority = false,
            bool $select_straight_join = false,
            $from_joined_tables = [],
            $from_join_types = [],
            $from_join_on_fields = [],
            $from_join_on_comparisons = [],
            $from_join_on_values = [],
            $where_fields = '',
            $where_comparison_operators = '',
            $where_values = '',
            $where_conditional_operators = ['AND'],
            $group_by_fields = '',
            $having_fields = '',
            $having_comparison_operators = '',
            $having_values = '',
            $having_conditional_operators = ['AND'],
            $order_by_fields = '',
            int $limit_limit = -1,
            int $limit_offset = -1,
            bool $return_string_only = false,
            bool $use_prepared_statements = false ) {

            /* Definition ************************************************/
            $select_statement = '';
            $pdo_statement;

            /* Processing ************************************************/
            /* Prepare to Build Statement -------------------------------*/
            /* Clear PDO Placeholders */
            $this->clear_pdo_placeholders();

            /* Build Statement ------------------------------------------*/
            $select_statement =
                $this->build_select_clause(
                    $select_fields,
                    $select_functions,
                    $select_all,
                    $select_distinct,
                    $select_high_priority,
                    $select_straight_join )
                . ' ';

            $select_statement .=
                $this->build_from_clause(
                    $from_table,
                    $from_joined_tables,
                    $from_join_types,
                    $from_join_on_fields,
                    $from_join_on_comparisons,
                    $from_join_on_values )
                . ' ';

            /* Conditionally Add WHERE Clause */
            if (
                '' !== $where_fields
                && '' !== $where_comparison_operators
                && '' !== $where_values ) {

                $select_statement .=
                    $this->build_where_clause(
                        $where_fields,
                        $where_comparison_operators,
                        $where_values,
                        $where_conditional_operators,
                        $use_prepared_statements )
                    . ' ';
            }

            /* Conditionally Add GROUP BY Clause */
            if ( '' !== $group_by_fields ) {

                $select_statement .=
                    $this->build_group_by_clause( $group_by_fields )
                    . ' ';
            }

            /* Conditionally Add HAVING Clause */
            if (
                '' !== $group_by_fields
                && '' !== $having_fields
                && '' !== $having_comparison_operators
                && '' !== $having_values ) {

                $select_statement .=
                    $this->build_having_clause(
                        $having_fields,
                        $having_comparison_operators,
                        $having_values,
                        $having_conditional_operators,
                        $use_prepared_statements )
                    . ' ';
            }

            /* Conditionally Add ORDER BY Clause */
            if ( '' !== $order_by_fields ) {

                $select_statement .=
                    $this->build_order_by_clause(
                        $order_by_fields )
                    . ' ';
            }

            /* Conditionally Add LIMIT Clause */
            if ( 0 <= $limit_limit ) {

                $select_statement .=
                    $this->build_limit_clause(
                        $limit_limit,
                        $limit_offset )
                    . ' ';
            }

            /* Remove Trailing Space ------------------------------------*/
            $select_statement = substr(
                $select_statement,
                0,
                strlen( $select_statement ) - 1 );

            /* Return ****************************************************/
            if ( $return_string_only ) {

                return $select_statement;
            }

            elseif ( $use_prepared_statements ) {

                $pdo_statement =
                    $this->pdo->prepare( $select_statement );

                if ( true === $this->execute_safe_query( $pdo_statement ) ) {

                    return $pdo_statement;
                }
                else {

                    return false;
                }
            }

            else {

                return $this->pdo->query( $select_statement );
            }
        }

        /******************************************************************
        * Protected Methods
        ******************************************************************/

        /** @method build_select_clause
         * Creates a SELECT clause string for use in a SELECT statement.
         * Does not allow the use of subqueries in the clause. Fields
         * should be validated prior to using this method.
         * @param string[] $fields
         * @param  string $functions
         * @return string
         */
        protected function build_select_clause(
            $fields,
            $functions = [''],
            bool $all = false,
            bool $distinct = false,
            bool $high_priority = false,
            bool $straight_join = false ) {

            /* Definition ************************************************/
            $select_clause = '';

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force $fields to be Array */
            if ( ! is_array( $fields ) ) {

                $fields = [ $fields ];
            }
            
            /* Force $functions to be Array */
            if ( ! is_array( $functions ) ) {

                $functions = [ $functions ];
            }

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
            $select_clause = 'SELECT ';

            /* Conditionally Add SELECT Clause Options */
            if ( $all ) {

                $select_clause .= 'ALL ';
            }

            if ( $distinct ) {

                $select_clause .= 'DISTINCT ';
            }

            if ( $high_priority ) {

                $select_clause .= 'HIGH_PRIORITY ';
            }

            if ( $straight_join ) {

                $select_clause .= 'STRAIGHT_JOIN ';
            }

            /* Process $fields If Fields Exist */
            if ( [] !== $fields ) {

                foreach ( $fields as $key => $field ) {

                    /* Build Fields into SELECT Clause */
                    // Add Field with Valid Function
                    if (
                        array_key_exists( $key, $functions )
                        && '' !== $functions[ $key ] ) {

                        $select_clause .=
                            $functions[ $key ] . '(';

                        if ( '*' === $field ) {

                            $select_clause .=
                                $field . '), ';
                        }
                        else {

                            $select_clause .=
                                $this->enclose_database_object_names(
                                    $field ) . '), ';
                        }
                    }

                    // Add Field without Function
                    else {

                        if ( '*' === $field ) {

                            $select_clause .=
                                $field . ', ';
                        }
                        else {

                            $select_clause .=
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
                $select_clause = substr(
                    $select_clause,
                    0,
                    strlen( $select_clause ) - 2 );
            }

            /* If No Fields, If Invalidated $fields Array, Use '*' */
            else {

                $select_clause .= '*';
            }

            /* Return ****************************************************/
            return $select_clause;
        }
       
        /*-----------------------------------------------------------------
         * Write Clause Building Methods
         *---------------------------------------------------------------*/

        /** @method build_group_by_clause
         * Creates a GROUP BY clause string for use within a selection
         * statement. Fields should be validated prior to using this
         * method.
         * @param array|string $fields
         * @return string
         */
        protected function build_group_by_clause(
            $fields ) {

            /* Definition ************************************************/
            $group_by_clause = '';

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force $fields to be Array */
            if ( ! is_array( $fields ) ) {

                $fields = [ $fields ];
            }

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
         * method.
         * @param mixed string string[] $fields
         * @param mixed string string[] $comparison_operators
         * @param mixed $values
         * @param string|array $conditional_operators
         * @param bool $use_prepared_statements
         * @return string
         * @return false
         */
        protected function build_having_clause(
            $fields,
            $comparison_operators,
            $values,
            $conditional_operators = ['AND'],
            bool $use_prepared_statements = false ) {

            /* Definition ************************************************/
            $having_clause = '';

            /* Processing ************************************************/
            /* Build WHERE Clause, Since Having is the Same */
            $having_clause =
                $this->build_where_clause(
                    $fields,
                    $comparison_operators,
                    $values,
                    $conditional_operators,
                    $use_prepared_statements );

            /* Replace WHERE with HAVING */
            $having_clause =
                str_replace( 'WHERE', 'HAVING', $having_clause );

            /* Return ****************************************************/
            return $having_clause;
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
