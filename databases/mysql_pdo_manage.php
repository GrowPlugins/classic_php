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

        /** @method build_create_table_statement
         * Creates a CREATE TABLE statement string. $field_options are not
         * validated for SQL validity, so be sure you pass the right
         * field options. It is highly suggested to use PDO parameter
         * placeholders (e.g., ':placeholder') for values, so you can
         * implement PDO prepared statements. However, this is not
         * required.
         * @param string $table
         * @param mixed string|array $fields
         * @param mixed string|array $data_types
         * @param mixed string|array $field_options
         * @param bool $check_existence
         * @return string
         */
        function build_create_table_statement(
            string $table,
            $fields,
            $data_types,
            $field_options = [],
            bool $check_existence = false ) {

            /* Definition ************************************************/
            $create_table_statement;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force Parameters to be Arrays */
            // Force $fields to be Array
            if ( ! is_array( $fields ) ) {

                $fields = [ $fields ];
            }
            
            // Force $data_types to be Array
            if ( ! is_array( $data_types ) ) {

                $data_types = [ $data_types ];
            }
            
            // Force $field_options to be Array
            if ( ! is_array( $field_options ) ) {

                $field_options = [ $field_options ];
            }

            /* Validate $fields */
            if (
                ! $this->arrays->validate_data_types(
                    $fields,
                    'string' ) ) {

                $fields = [];
            }

            /* Validate $data_types */
            $data_types = $this->validate_sql_data_types( $data_types );

            if ( [] === $data_types ) {

                return false;
            }

            /* Validate $field_options */
            if (
                ! $this->arrays->validate_data_types(
                    $field_options,
                    'string' ) ) {

                return false;
            }

            /* Build Clause ---------------------------------------------*/
            $create_table_statement = 'CREATE TABLE ';

            if ( $check_existence ) {

                $create_table_statement .= 'IF NOT EXISTS ';
            }

            $create_table_statement .= $table;

            /* Build Fields List */
            $create_table_statement .= ' (';

            foreach ( $fields as $key => $field ) {

                if (
                    array_key_exists( $key, $fields )
                    && array_key_exists( $key, $data_types ) ) {

                    $create_table_statement .=
                        $this->enclose_database_object_names(
                            $fields[ $key ] )
                        . ' ' . $data_types[ $key ];

                    if ( array_key_exists( $key, $field_options ) ) {

                        $create_table_statement .=
                            ' ' . strtoupper( $field_options[ $key ] );
                    }

                    $create_table_statement .= ', ';
                }
            }

            // Remove Trailing ', '
            $create_table_statement = substr(
                $create_table_statement,
                0,
                strlen( $create_table_statement ) - 2 );

            $create_table_statement .= ')';

            /* Return ****************************************************/
            return $create_table_statement;
        }

        /** @method build_drop_table_statement
         * Creates a DROP TABLE statement string. The table should be
         * validated prior to using this method.
         * @param string $table
         * @return string
         */
        function build_drop_table_statement(
            $tables,
            bool $check_existence = false ) {

            /* Definition ************************************************/
            $drop_table_statement;

            /* Processing ************************************************/
            /* Validation ************************************************/
            /* Force Parameters to be Arrays */
            // Force $tables to be Array
            if ( ! is_array( $tables ) ) {

                $tables = [ $tables ];
            }

            /* Validate $tables */
            if (
                ! $this->arrays->validate_data_types(
                    $tables,
                    'string' ) ) {

                return false;
            }

            /* Build Clause ---------------------------------------------*/
            $drop_table_statement =
                'DROP TABLE ';

            /* Conditionally Add Table Check */
            if ( $check_existence ) {

                $drop_table_statement .= 'IF EXISTS ';
            }

            foreach ( $tables as $table ) {

                $drop_table_statement .=
                    $this->enclose_database_object_names( $table )
                    . ', ';
            }

            // Remove Trailing ', '
            $drop_table_statement = substr(
                $drop_table_statement,
                0,
                strlen( $drop_table_statement ) - 2 );

            /* Return ****************************************************/
            return $drop_table_statement;
        }

        /******************************************************************
        * Private Methods
        ******************************************************************/

        /*-----------------------------------------------------------------
         * General Validation/Building Methods
         *---------------------------------------------------------------*/

        /** @method validate_sql_data_types
         * Ensures the data type(s) provided are valid SQL data types.
         * @param mixed string string[] $fields
         * @param mixed string string[] $comparison_operators
         * @param mixed string string[] $values
         * @param string[] $conditional_operators
         * @return string
         */
        private function validate_sql_data_types( $data_types ) {

            /* Definition ************************************************/
            $mysql_data_types =
                $this->read_json_file(
                    CLASSIC_PHP_DIR
                    . '/classic_php_data_files/mysql_data_types.json' );
            $data_type_valid = false;
            $open_parenthesis_found = false;
            $close_parenthesis_found = false;
            $open_parenthesis_position = 0;
            $close_parenthesis_position = 0;
            $parentheses_position = 0;
            $data_type_parentheses_presence = 'not allowed';

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force Parameters to be Arrays */
            // Force $data_types to be Array
            if ( ! is_array( $data_types ) ) {

                $data_types = [ $data_types ];
            }

            /* Validate $data_types -------------------------------------*/
            foreach( $data_types as $key => $data_type ) {

                $data_type_valid = false;
                $data_type_parentheses_presence = 'not allowed';

                /* Force All Data Types to Uppercase */
                $data_types[ $key ] = strtoupper( $data_types[ $key ] );

                /* Search in $data_types for Parenthesis */
                $open_parenthesis_position =
                    strpos( $data_types[ $key ], '(' );
                
                $close_parenthesis_position =
                    strpos( $data_types[ $key ], ')' );

                // Record If Parenthesis Found
                if ( false !== $open_parenthesis_position ) {

                    $open_parenthesis_found = true;
                }

                if ( false !== $close_parenthesis_position ) {

                    $close_parenthesis_found = true;
                }

                // Record Where Parentheses Begin
                // Open Parenthesis Comes First
                if (
                    $open_parenthesis_found
                    && $close_parenthesis_found
                    && $close_parenthesis_position > $open_parenthesis_position ) {

                    $parentheses_position = $open_parenthesis_position;
                }

                // Closed Parenthesis Comes First
                elseif (
                    $open_parenthesis_found
                    && $close_parenthesis_found ) {

                    $parentheses_position = $close_parenthesis_position;
                }

                // Only Open Parenthesis Found
                elseif ( $open_parenthesis_found ) {

                    $parentheses_position = $open_parenthesis_position;
                }

                // Only Close Parenthesis Found
                elseif ( $close_parenthesis_found ) {

                    $parentheses_position = $close_parenthesis_position;
                }

                // No Parenthesis Found
                else {

                    $parentheses_position =
                        strlen( $data_types[ $key ] );
                }

                /* Compare $data_types to $mysql_data_types */
                foreach( $mysql_data_types as $mysql_data_type ) {

                    // Compare $data_types to $mysql_data_types->name
                        // Without Parenthesis
                    if (
                        $mysql_data_type->name ===
                            substr(
                                $data_types[ $key ],
                                0,
                                $parentheses_position ) ) {

                        $data_type_parentheses_presence =
                            $mysql_data_type->parenthesis;

                        $data_type_valid = true;

                        break;
                    }

                    // Compare $data_types to $mysql_data_types->synonyms
                    else {

                        foreach (
                            $mysql_data_type->synonyms
                            as $synonym ) {

                            if (
                                $synonym ===
                                    substr(
                                        $data_types[ $key ],
                                        0,
                                        $open_parenthesis_position ) ) {
        
                                        $data_type_parentheses_presence =
                                            $mysql_data_type->parenthesis;
                
                                        $data_type_valid = true;
                
                                        break;
                            }
                        }
                    }
                }

                /* Validate Based on Parentheses */
                if ( $data_type_valid ) {

                    // Invalidate If Parentheses Required and Missing Open
                    if (
                        'required' === $data_type_parentheses_presence
                        && ! $open_parenthesis_found ) {

                        $data_type_valid = false;
                    }
                    
                    // Invalidate If Parentheses Required and Missing Close
                    elseif (
                        'required' === $data_type_parentheses_presence
                        && ! $close_parenthesis_found ) {

                        $data_type_valid = false;
                    }

                    // Invalidate if Parantheses Not Allowed and Open Present
                    elseif (
                        'not allowed' === $data_type_parentheses_presence
                        && $open_parenthesis_found ) {

                        $data_type_valid = false;
                    }

                    // Invalidate if Parantheses Not Allowed and Close Present
                    elseif (
                        'not allowed' === $data_type_parentheses_presence
                        && $close_parenthesis_found ) {

                        $data_type_valid = false;
                    }

                    // Invalidate if Open Paranthesis Present Without Close
                    elseif (
                        $open_parenthesis_found
                        && ! $close_parenthesis_found ) {

                        $data_type_valid = false;
                    }

                    // Invalidate if Close Paranthesis Present Without Open
                    elseif (
                        $close_parenthesis_found
                        && ! $open_parenthesis_found ) {

                        $data_type_valid = false;
                    }

                    // Invalidate if Parantheses Present and in Wrong Order
                    elseif (
                        $open_parenthesis_found
                        && $close_parenthesis_found
                        && $close_parenthesis_position
                                < $open_parenthesis_position ) {

                        $data_type_valid = false;
                    }
                }

                /* If No Valid Data Type Found, Mark $data_types Value */
                if ( ! $data_type_valid ) {

                    $this->arrays->mark_value_null( $data_types, $key );
                }
            }

            /* Remove All Invalid, Marked, $data_types Values -----------*/
            $this->arrays->remove_null_values( $data_types );

            /* Return ****************************************************/
            return $data_types;
        }
    }
}
