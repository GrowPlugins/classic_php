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
            $data_types,
            $field_options = [],
            bool $check_existence = false ) {

            /* Definition ************************************************/
            $create_table_clause;

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
            $data_types = $this->validate_data_types( $data_types );

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

        /******************************************************************
        * Private Methods
        ******************************************************************/

        /*-----------------------------------------------------------------
         * General Validation/Building Methods
         *---------------------------------------------------------------*/

        /** @method validate_data_types
         * Ensures the data type(s) provided 
         * @param mixed string string[] $fields
         * @param mixed string string[] $comparison_operators
         * @param mixed string string[] $values
         * @param string[] $conditional_operators
         * @return string
         */
        private function validate_data_types( $data_types ) {

            /* Definition ************************************************/
            $mysql_data_types =
                $this->read_json_file(
                    CLASSIC_PHP_DIR
                    . '/classic_php_data_files/mysql_data_types.json' );
            $data_type_valid = false;
            $parenthesis_found = false;
            $open_parenthesis_position = 0;
            $data_type_parenthesis_presence = 'not allowed';

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
                $parenthesis_found = false;
                $data_type_parenthesis_presence = 'not allowed';

                /* Force All Data Types to Uppercase */
                $data_types[ $key ] = strtoupper( $data_types[ $key ] );

                // Search in $data_types for '(', Record If Found
                $open_parenthesis_position =
                    strpos( $data_types[ $key ], '(' );

                if ( false === $open_parenthesis_position ) {

                    $open_parenthesis_position =
                        strlen( $data_types[ $key ] );
                }
                else {
                    
                    $parenthesis_found = true;
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
                                $open_parenthesis_position ) ) {

                        $data_type_parenthesis_presence =
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
        
                                        $data_type_parenthesis_presence =
                                            $mysql_data_type->parenthesis;
                
                                        $data_type_valid = true;
                
                                        break;
                            }
                        }
                    }
                }

                /* Validate Based on Parenthesis */
                // Invalidate If Parenthesis Required and Missing
                if (
                    $data_type_valid
                    && 'required' === $data_type_parenthesis_presence
                    && ! $parenthesis_found ) {

                    $data_type_valid = false;
                }

                // Invalidate if Paranthesis Not Allowed and Present
                elseif (
                    $data_type_valid
                    && 'not allowed' === $data_type_parenthesis_presence
                    && $parenthesis_found
                ) {

                    $data_type_valid = false;
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
