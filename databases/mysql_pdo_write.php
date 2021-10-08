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
    Write Queries:
        UPDATE table
            SET field = value
        WHERE field = value

        INSERT INTO table
        (fields)
        VALUES (values)

        DELETE FROM table
        WHERE field = value
*/

/**************************************************************************
 * Class Definition -------------------------------------------------------
 *************************************************************************/
if ( ! class_exists( '\ClassicPHP\MySQLPDO_Write' ) ) {

    /** Class: MySQLPDO_Write
     * Helps you more quickly change database data safely using PDO.
     * Inherits From: ClassicPHP\MySQLPDO
     * Requires: \PDO, ClassicPHP\ArrayProcessing
     * Inherited By: None
     */
    class MySQLPDO_Write extends MySQLPDO {

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

        /** @method update_query
         * Sends a query to the database and updates records in the
         * database immediately, or returns a PDOStatement object for later
         * execution.
         * 
         * With $use_prepared_statements false (not recommended), the
         * update query is executed immediately and true is returned. With
         * $use_prepared_statements true (highly recommended), a
         * PDOStatement object is returned. When a PDOStatement object is
         * returned, you must use the MySQLPDO::execute_safe_query() method
         * on the PDOStatement object to actually send the query to the
         * database for execution. Note that you can store the PDOStatement
         * object somewhere and simply re-call
         * MySQLPDO::execute_safe_query() each time you want to send a new
         * query to the database using the same query, where only the SET
         * or WHERE values are different. See:
         * https://www.php.net/manual/en/pdo.prepared-statements.php
         * 
         * Optionally return a query string instead of a
         * PDOStatement, by setting the $return_string_only argument to
         * true.
         * 
         * @param string $table
         * @param array $set_fields
         * @param array $set_values
         * @param $where_fields
         * @param $where_comparison_operators
         * @param $where_values
         * @param $where_conditional_operators
         * @param $order_by_fields
         * @param int $limit
         * @param int $offset
         * @param bool $return_string_only
         * @param bool $use_prepared_statements
         * @return bool|PDOStatement|string
         */
        function update_query(
            string $table,
            array $set_fields,
            array $set_values = [],
            $where_fields = [],
            $where_comparison_operators = [],
            $where_values = [],
            $where_conditional_operators = ['AND'],
            $order_by_fields = [],
            int $limit = -1,
            int $offset = -1,
            bool $return_string_only = false,
            bool $use_prepared_statements = false ) {

            /* Definition ************************************************/
            $update_statement = '';
            $set_clause;
            $where_clause;
            $order_by_clause;
            $limit_clause;

            /* Processing ************************************************/
            /* Prepare to Build Statement -------------------------------*/
            /* Clear PDO Placeholders */
            $this->clear_pdo_placeholders();

            /* Build Statement ------------------------------------------*/
            /* Build Statement, If Required Values Exist */
            if (
                [] !== $set_fields
                && ! empty( $set_fields ) ) {

                $update_statement =
                    'UPDATE '
                    . $this->enclose_database_object_names( $table );

                // Add SET Clause
                $set_clause =
                    $this->build_set_clause(
                        $set_fields,
                        $set_values,
                        $use_prepared_statements );
                
                if ( false !== $set_clause ) {

                    $update_statement .=
                        ' ' . $set_clause;
                }

                // Conditionally Add WHERE Clause
                if (
                    [] !== $where_fields
                    && ! empty( $where_fields )
                    && [] !== $where_comparison_operators
                    && ! empty( $where_comparison_operators )
                    && [] !== $where_values
                    && ! empty( $where_values ) ) {

                    $where_clause =
                        $this->build_where_clause(
                            $where_fields,
                            $where_comparison_operators,
                            $where_values,
                            $where_conditional_operators,
                            $use_prepared_statements );

                    if ( false !== $where_clause ) {

                        $update_statement .=
                            ' ' . $where_clause;
                    }
                }

                // Conditionally Add ORDER BY Clause
                if (
                    [] !== $order_by_fields 
                    && ! empty( $order_by_fields ) ) {

                    $order_by_clause =
                        $this->build_order_by_clause( $order_by_fields );

                    if ( false !== $order_by_clause ) {

                        $update_statement .=
                            ' ' . $order_by_clause;
                    }
                }

                // Conditionally Add LIMIT Clause
                if ( -1 < $limit ) {

                    $limit_clause =
                        $this->build_limit_clause(
                            $limit,
                            $offset );

                    if ( false !== $limit_clause ) {

                        $update_statement .=
                            ' ' . $limit_clause;
                    }
                }
            }

            /* Return ****************************************************/
            if ( $return_string_only ) {

                return $update_statement;
            }
            else {

                if ( $use_prepared_statements ) {

                    return $this->pdo->prepare( $update_statement );
                }
                else {

                    $this->pdo->query( $update_statement );

                    return true;
                }
            }
        }

        /** @method insert_into_query
         * Sends a query to the database and inserts records into the
         * database immediately, or returns a PDOStatement object for later
         * execution.
         * 
         * With $use_prepared_statements false (not recommended), the
         * insert query is executed immediately and true is returned. With
         * $use_prepared_statements true (highly recommended), a
         * PDOStatement object is returned. When a PDOStatement object is
         * returned, you must use the MySQLPDO::execute_safe_query() method
         * on the PDOStatement object to actually send the query to the
         * database for execution. Note that you can store the PDOStatement
         * object somewhere and simply re-call
         * MySQLPDO::execute_safe_query() each time you want to send a new
         * query to the database using the same query, where only the SET
         * values are different. See:
         * https://www.php.net/manual/en/pdo.prepared-statements.php
         * 
         * Optionally return a query string instead of a
         * PDOStatement, by setting the $return_string_only argument to
         * true.
         * 
         * @param string $table,
         * @param $set_fields,
         * @param $set_values = [],
         * @param bool $return_string_only = false,
         * @param bool $use_prepared_statements = false,

         * @param string $priority = '',
         * @param bool $delayed_insert = false,
         * @param bool $ignore_errors = false
         * @return bool|PDOStatement|string
         */
        function insert_into_query(
            string $table,
            $set_fields,
            $set_values = [],
            bool $return_string_only = false,
            bool $use_prepared_statements = false,

            string $priority = '',
            bool $delayed_insert = false,
            bool $ignore_errors = false ) {

            /* Definition ************************************************/
            $insert_into_statement;
            $set_clause;

            /* Processing ************************************************/
            /* Validation ************************************************/
            /* Validate $priority */
            if (
                'low' === strtolower( $priority )
                || 'low_priority' === strtolower( $priority ) ) {

                $priority = 'LOW_PRIORITY';
            }
            elseif (
                'high' === strtolower( $priority )
                || 'high_priority' === strtolower( $priority ) ) {

                $priority = 'HIGH_PRIORITY';
            }
            else {

                $priority = '';
            }

            /* Prepare to Build Statement -------------------------------*/
            /* Clear PDO Placeholders */
            $this->clear_pdo_placeholders();

            /* Build Statement ------------------------------------------*/
            /* Build Statement, If Required Values Exist */
            if (
                [] !== $set_fields
                && ! empty( $set_fields ) ) {

                $insert_into_statement =
                    'INSERT ';

                // Conditionally Add $priority to Statement
                if ( '' !== $priority ) {

                    $insert_into_statement .= $priority . ' ';
                }

                // Conditionally Add DELAYED to Statement
                if ( true === $delayed_insert ) {

                    $insert_into_statement .= 'DELAYED ';
                }

                // Conditionally Add DELAYED to Statement
                if ( true === $ignore_errors ) {

                    $insert_into_statement .= 'IGNORE ';
                }

                // Add Table Name
                $insert_into_statement .=
                    'INTO '
                    . $this->enclose_database_object_names( $table );

                // Add SET Clause
                $set_clause =
                    $this->build_set_clause(
                        $set_fields,
                        $set_values,
                        $use_prepared_statements );
                
                if ( false !== $set_clause ) {

                    $insert_into_statement .=
                        ' ' . $set_clause;
                }
            }

            /* Return ****************************************************/
            if ( $return_string_only ) {

                return $insert_into_statement;
            }
            else {

                if ( $use_prepared_statements ) {

                    return $this->pdo->prepare( $insert_into_statement );
                }
                else {

                    $this->pdo->query( $insert_into_statement );

                    return true;
                }
            }
        }

        /** @method build_delete_statement
         * Sends a query to the database and deletes records from the
         * database immediately, or returns a PDOStatement object for later
         * execution.
         * 
         * With $use_prepared_statements false (not recommended), the
         * delete query is executed immediately and true is returned. With
         * $use_prepared_statements true (highly recommended), a
         * PDOStatement object is returned. When a PDOStatement object is
         * returned, you must use the MySQLPDO::execute_safe_query() method
         * on the PDOStatement object to actually send the query to the
         * database for execution. Note that you can store the PDOStatement
         * object somewhere and simply re-call
         * MySQLPDO::execute_safe_query() each time you want to send a new
         * query to the database using the same query, where only the WHERE
         * values are different. See:
         * https://www.php.net/manual/en/pdo.prepared-statements.php
         * 
         * Optionally return a query string instead of a
         * PDOStatement, by setting the $return_string_only argument to
         * true.
         * 
         * @param string $table
         * @param string|array $where_fields
         * @param string|array $where_comparison_operators
         * @param string|array $where_values
         * @param string|array $where_conditional_operators
         * @param string|array $order_by_fields
         * @param int $limit_limit
         * @param int $limit_offset
         * @param bool $low_priority
         * @param bool $quick
         * @param bool $ignore
         * @return string
         */
        function build_delete_statement(
            string $table,
            $where_fields = '',
            $where_comparison_operators = '',
            $where_values = '',
            $where_conditional_operators = ['AND'],
            $order_by_fields = '',
            int $limit_limit = -1,
            int $limit_offset = -1,
            bool $return_string_only = false,
            bool $use_prepared_statements = false,
            bool $low_priority = false,
            bool $quick = false,
            bool $ignore = false ) {

            /* Definition ************************************************/
            $delete_statement;

            /* Processing ************************************************/
            /* Prepare to Build Statement -------------------------------*/
            /* Clear PDO Placeholders */
            $this->clear_pdo_placeholders();

            /* Build Clause ---------------------------------------------*/
            $delete_statement =
                'DELETE ';

            /* Conditionally Add DELETE Options */
            if ( $low_priority ) {

                $delete_statement .= 'LOW_PRIORITY ';
            }

            if ( $quick ) {

                $delete_statement .= 'QUICK ';
            }

            if ( $ignore ) {

                $delete_statement .= 'IGNORE ';
            }

            /* Add Selected Table to Statement */
            $delete_statement .=
                'FROM '
                . $this->enclose_database_object_names( $table );

            /* Conditionally Add WHERE Clause */
            if (
                '' !== $where_fields
                || '' !== $where_comparison_operators
                || '' !== $where_values ) {

                $delete_statement .=
                    ' '
                    . $this->build_where_clause(
                        $where_fields,
                        $where_comparison_operators,
                        $where_values,
                        $where_conditional_operators,
                        $use_prepared_statements );
            }

            /* Conditionally Add ORDER BY Clause */
            if ( '' !== $order_by_fields ) {

                $delete_statement .=
                    ' '
                    . $this->build_order_by_clause(
                        $order_by_fields );
            }

            /* Conditionally Add LIMIT Clause */
            if ( -1 < $limit_limit ) {

                $delete_statement .=
                    ' '
                    . $this->build_limit_clause(
                        $limit_limit,
                        $limit_offset );
            }

            /* Return ****************************************************/
            if ( $return_string_only ) {

                return $delete_statement;
            }

            else {

                if ( $use_prepared_statements ) {

                    return $this->pdo->prepare( $delete_statement );
                }
                else {

                    $this->pdo->query( $delete_statement );

                    return true;
                }
            }
        }

        /******************************************************************
        * Protected Methods
        ******************************************************************/
       
        /*-----------------------------------------------------------------
         * Write Clause Building Methods
         *---------------------------------------------------------------*/

        /** @method build_set_clause
         * Creates a SET clause string for use in write-type query
         * statements.
         * @param string[] $set_fields
         * @param string[] $set_values          // Values sought in SET
         * @return string
         */
        protected function build_set_clause(
            $set_fields,
            $set_values = [],
            bool $use_prepared_statements = false ) {

            /* Definition ************************************************/
            $set_clause = '';

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force $set_fields to be Array */
            if ( ! is_array( $set_fields ) ) {

                $set_fields = [ $set_fields ];
            }

            /* Force $set_values to be Array */
            if ( ! is_array( $set_values ) ) {

                $set_values = [ $set_values ];
            }

            /* Validate $set_fields */
            if (
                ! $this->arrays->validate_data_types(
                    $set_fields,
                    'string' ) ) {

                $set_fields = [];
            }

            /* Validate $set_values */
            if (
                ! $this->arrays->validate_data_types(
                    $set_values,
                    ['string', 'int', 'float', 'bool'] ) ) {

                $set_values = [];
            }

            /* Conditionally Use PDO Prepared Statement Placeholders ----*/
            if ( $use_prepared_statements ) {

                $set_values =
                    $this->create_pdo_placeholder_values(
                        $set_values );
            }

            /* Build Clause ---------------------------------------------*/
            /* Build SET Clause, If Fields Exist */
            if ( [] !== $set_fields ) {

                $set_clause =
                    'SET ';

                foreach ( $set_fields as $key => $set_field ) {

                    // Add Next Field, Operator, and Value, If All Exist
                    if ( array_key_exists( $key, $set_values ) ) {

                        $set_clause .=
                            $this->enclose_database_object_names(
                                $set_fields[ $key ] )
                            . ' = '
                            . $this->prepare_values_for_query(
                                $set_values[ $key ] ) . ', ';
                    }
                }

                // Remove Trailing ', '
                $set_clause = substr(
                    $set_clause,
                    0,
                    strlen( $set_clause ) - 2 );
            }

            /* Return ****************************************************/
            return $set_clause;
        }
    }
}
