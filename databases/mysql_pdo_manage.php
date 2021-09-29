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
    Manage Queries:
        CREATE table [IF NOT EXISTS]
        (fields)
        VALUES (values)

        ALTER TABLE table
        [ADD fieldName fieldDefinition [FIRST | AFTER fieldName]][,
        [MODIFY fieldName fieldDefinition [FIRST | AFTER fieldName]]]
        |
        [CHANGE COLUMN originalFieldName newFieldName fieldDefinition [FIRST | AFTER fieldName]]
        |
        [DROP COLUMN fieldName]
        |
        [RENAME TO tableName]

        DROP table
*/

/**************************************************************************
 * Class Definition -------------------------------------------------------
 *************************************************************************/
if ( ! class_exists( '\ClassicPHP\MySQLPDO_Manage' ) ) {

    /** Class: MySQLPDO_Manage
     * Helps you more quickly manage database tables.
     * Inherits From: ClassicPHP\MySQLPDO
     * Requires: \PDO, ClassicPHP\ArrayProcessing
     * Inherited By: None
     */
    class MySQLPDO_Manage extends MySQLPDO {

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

        /** @method build_create_table_clause
         * Creates a CREATE clause string. It is highly suggested to use
         * PDO parameter placeholders (e.g., ':placeholder') for values,
         * so you can implement PDO prepared statements. However, this is
         * not required.
         * @param mixed string string[] $fields
         * @param mixed string string[] $comparison_operators
         * @param mixed string string[] $values
         * @param string[] $conditional_operators
         * @return string
         */
        function build_create_table_clause(
            string $table,
            $fields,
            array $data_types,
            array $field_options = [],
            bool $check_existence = false ) {

            /* Definition ************************************************/
            $create_table_clause;
            $mysql_data_types =
                $this->read_json_file(
                    CLASSIC_PHP_DIR
                    . '/classic_php_data_files/mysql_data_types.json' );
            $data_type_valid = false;
            $data_type_open_parenthesis_position = 0;

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

            /* Validate $data_types */
            foreach( $data_types as $key => $data_type ) {

                $data_type_valid = false;

                $data_types[ $key ] = strtoupper( $data_types[ $key ] );

                // Compare $data_types to $mysql_data_types
                foreach( $mysql_data_types as $mysql_data_type ) {

                    // Search in $data_types for '('
                    $data_type_open_parenthesis_position =
                        strpos( $data_types[ $key ], '(' );

                    if ( false === $data_type_open_parenthesis_position ) {

                        $data_type_open_parenthesis_position =
                            strlen( $data_types[ $key ] );
                    }

                    // Compare Uppercase $data_types to $mysql_data_types
                        // Without Parenthesis
                    if (
                        $mysql_data_type ===
                            substr(
                                $data_types[ $key ],
                                0,
                                $data_type_open_parenthesis_position ) ) {

                        $data_type_valid = true;
                    }
                }

                // If No Valid Data Type Found, Mark $data_types Value
                if ( ! $data_type_valid ) {

                    $this->arrays->mark_value_null( $data_types, $key );
                }
            }

            // Remove Invalid, Marked, $data_types Values
            $this->arrays->remove_null_values( $data_types );

            /* Validate $field_options */
            if (
                ! $this->arrays->validate_data_types(
                    $field_options,
                    'string' ) ) {

                $field_options = [];
            }

            /* Build Clause ---------------------------------------------*/
            $create_table_clause = 'CREATE TABLE ';

            if ( $check_existence ) {

                $create_table_clause .= 'IF NOT EXISTS ';
            }

            $create_table_clause .= $table;

            /* Build Fields List */
            $create_table_clause .= ' (';

            foreach ( $fields as $key => $field ) {

                if (
                    array_key_exists( $key, $fields )
                    && array_key_exists( $key, $data_types ) ) {

                    $create_table_clause .=
                        $this->enclose_database_object_names(
                            $fields[ $key ] )
                        . ' ' . $data_types[ $key ];

                    if ( array_key_exists( $key, $field_options ) ) {

                        $create_table_clause .=
                            ' ' . $field_options[ $key ];
                    }

                    $create_table_clause .= ', ';
                }
            }

            // Remove Trailing ', '
            $create_table_clause = substr(
                $create_table_clause,
                0,
                strlen( $create_table_clause ) - 2 );

            $create_table_clause .= ')';

            /* Return ****************************************************/
            return $create_table_clause;
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
