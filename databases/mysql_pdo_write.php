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

/*
    Write Queries:
        UPDATE table
            SET field = value
        WHERE field = value

        INSERT INTO table
        (fields)
        VALUES (values)

        DELETE table
        WHERE field = value
*/

/**************************************************************************
 * Class Definition -------------------------------------------------------
 *************************************************************************/
if ( ! class_exists( 'MySQLPDO_Write' ) ) {

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

        /** @method build_update_clause
         * Creates an UPDATE clause string for use within an update
         * statement. Tables and fields should be validated prior to
         * using this method. It is highly suggested to use PDO parameter
         * placeholders (e.g., ':placeholder') for values, so you can
         * implement PDO prepared statements. However, this is not
         * required.
         * @param string $table
         * @param string[] $set_fields
         * @param string[] $set_comparisons     // Comparison Operators
         * @param string[] $set_values          // Values sought in SET
         * @return string
         */
        function build_update_clause(
            string $table,
            array $set_fields,
            array $set_comparisons = [],
            array $set_values = [] ) {

            /* Definition ************************************************/
            $update_clause = '';

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $set_fields */
            if (
                ! $this->arrays->validate_data_types(
                    $set_fields,
                    'string' ) ) {

                $set_fields = [];
            }

            /* Validate $set_comparisons */
            if (
                $this->arrays->validate_data_types(
                    $set_comparisons,
                    'string' ) ) {

                // Validate Each SET Comparison Operator
                foreach (
                    $set_comparisons as $key => $join_on_comparison ) {

                    if (
                        '=' !== $set_comparisons[ $key ]
                        && '<' !== $set_comparisons[ $key ]
                        && '>' !== $set_comparisons[ $key ]
                        && '<=' !== $set_comparisons[ $key ]
                        && '>=' !== $set_comparisons[ $key ]
                        && '<>' !== $set_comparisons[ $key ]
                        && '!=' !== $set_comparisons[ $key ] ) {

                        $set_comparisons[ $key ] = '=';
                    }
                }
            }
            else {

                $set_comparisons = [];
            }

            /* Validate $set_values */
            if (
                ! $this->arrays->validate_data_types(
                    $set_values,
                    ['string', 'int', 'float', 'bool'] ) ) {

                $set_values = [];
            }

            /* Build Clause ---------------------------------------------*/
            /* Build UPDATE Clause, If Fields Exist */
            if ( [] !== $set_fields ) {

                $update_clause =
                    'UPDATE '
                    . $this->enclose_database_object_names( $table )
                    . ' SET ';

                foreach ( $set_fields as $key => $set_field ) {

                    // Add Next Field, Operator, and Value, If All Exist
                    if (
                        array_key_exists( $key, $set_comparisons )
                        && array_key_exists( $key, $set_values ) ) {

                        $update_clause .=
                            $this->enclose_database_object_names(
                                $set_fields[ $key ] ) . ' '
                            . $set_comparisons[ $key ] . ' '
                            . $this->prepare_values_for_query(
                                $set_values[ $key ] ) . ', ';
                    }
                }

                // Remove Trailing ', '
                $update_clause = substr(
                    $update_clause,
                    0,
                    strlen( $update_clause ) - 2 );
            }

            /* Return ****************************************************/
            return $update_clause;
        }

        /** @method build_where_clause
         * Creates a WHERE clause string for use within an update
         * statement. Fields should be validated prior to using this
         * method. It is highly suggested to use PDO parameter
         * placeholders (e.g., ':placeholder') for values, so you can
         * implement PDO prepared statements. However, this is not
         * required.
         * @param mixed string string[] $fields
         * @param mixed string string[] $comparison_operators
         * @param mixed string string[] $values
         * @param string[] $conditional_operators
         * @return string
         */
        function build_where_clause(
            $fields,
            $comparison_operators,
            $values,
            array $conditional_operators = ['AND'] ) {

            /* Definition ************************************************/
            $where_clause;
            $condition_list_returned_value;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force $fields to be Array */
            if ( ! is_array( $fields ) ) {

                $fields = [ $fields ];
            }

            /* Force $comparison_operators to be Array */
            if ( ! is_array( $comparison_operators ) ) {

                $comparison_operators = [ $comparison_operators ];
            }

            /* Force $values to be Array */
            if ( ! is_array( $values ) ) {

                $values = [ $values ];
            }

            /* Build Clause ---------------------------------------------*/
            $where_clause = 'WHERE ';

            /* Build WHERE Conditions */
            $condition_list_returned_value = $this->build_condition_list(
                $fields,
                $comparison_operators,
                $values,
                $conditional_operators );

            if ( false !== $condition_list_returned_value ) {

                $where_clause .= $condition_list_returned_value;
            }
            else {

                return false;
            }

            /* Return ****************************************************/
            return $where_clause;
        }

        /** @method build_insert_into_clause
         * Creates a WHERE clause string for use within an update
         * statement. Fields should be validated prior to using this
         * method. It is highly suggested to use PDO parameter
         * placeholders (e.g., ':placeholder') for values, so you can
         * implement PDO prepared statements. However, this is not
         * required.
         * @param mixed string string[] $fields
         * @param mixed string string[] $comparison_operators
         * @param mixed string string[] $values
         * @param string[] $conditional_operators
         * @return string
         */
        function build_insert_into_clause(
            string $table,
            $fields,
            $values ) {

            /* Definition ************************************************/
            $insert_into_clause;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $fields */
            if (
                ! $this->arrays->validate_data_types(
                    $fields,
                    'string' ) ) {

                if ( is_string( $fields ) ) {

                    $fields = [ $fields ];
                }
                else {

                    $fields = [];
                }
            }

            /* Force $values to be Array */
            if ( ! is_array( $values ) ) {

                $values = [ $values ];
            }

            /* Build Clause ---------------------------------------------*/
            $insert_into_clause = 'INSERT INTO ' . $table;

            /* Build Fields List */
            $insert_into_clause .= ' (';

            foreach ( $fields as $key => $field ) {

                if ( array_key_exists( $key, $values ) ) {

                    $insert_into_clause .=
                        $this->enclose_database_object_names( $field )
                        . ', ';
                }
            }

            // Remove Trailing ', '
            $insert_into_clause = substr(
                $insert_into_clause,
                0,
                strlen( $insert_into_clause ) - 2 );

            $insert_into_clause .= ') ';

            /* Build Values List */
            $insert_into_clause .= 'VALUES (';

            foreach ( $values as $key => $value ) {

                if ( array_key_exists( $key, $fields ) ) {

                    $insert_into_clause .=
                        $this->prepare_values_for_query( $value )
                        . ', ';
                }
            }

            // Remove Trailing ', '
            $insert_into_clause = substr(
                $insert_into_clause,
                0,
                strlen( $insert_into_clause ) - 2 );

            $insert_into_clause .= ')';

            /* Return ****************************************************/
            return $insert_into_clause;
        }

        /** @method build_delete_clause
         * Creates a DELETE clause string for use within an update
         * statement. The table should be validated prior to using this
         * method.
         * @param string $table
         * @return string
         */
        function build_delete_clause( string $table ) {

            /* Definition ************************************************/
            $delete_clause;

            /* Processing ************************************************/
            /* Build Clause ---------------------------------------------*/
            $delete_clause =
                'DELETE '
                . $this->enclose_database_object_names( $table );

            /* Return ****************************************************/
            return $delete_clause;
        }
    }
}
