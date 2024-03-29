<?php

namespace ClassicPHP\V0_3_0;

/**************************************************************************
 * Class Header -----------------------------------------------------------
 *************************************************************************/
/* Class Using Aliases */
use \PDO as PDO;

/* Class Includes */
// Includes List
require_once(
    strstr(
        __DIR__,
        'classic_php',
        true ) .
    'classic_php/V0_3_0/databases/mysql_pdo.php' );

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
        [CHANGE COLUMN originalFieldName newFieldName fieldDefinition
            [FIRST | AFTER fieldName]]
        |
        [DROP COLUMN fieldName]
        |
        [RENAME TO tableName]

        DROP TABLE table
*/

/**************************************************************************
 * Class Definition -------------------------------------------------------
 *************************************************************************/
if ( ! class_exists( '\ClassicPHP\V0_3_0\MySQLPDO_Manage' ) ) {

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

        /** @method create_table_query
         * Sends a query to the database and creates a table in the
         * database immediately, returning true. Returns false if\
         * incorrect arguments are provided.
         * 
         * Optionally return a query string by setting the
         * $return_string_only argument to true. This is optional, instead
         * of sending a query directly to the database.
         * 
         * @param string $table
         * @param mixed string|array $fields
         * @param mixed string|array $data_types
         * @param mixed string|array $field_options
         * @param bool $check_existence
         * @param bool $return_string_only
         * @return string|false
         */
        function create_table_query(
            string $table,
            $fields,
            $data_types,
            $field_options = [],
            bool $check_existence = false,
            bool $return_string_only = false ) {

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

            if ( false === $data_types ) {

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

            $create_table_statement .=
                $this->enclose_database_object_names( $table );

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
            if ( $return_string_only ) {

                return $create_table_statement;

            }
            else {

                $this->pdo->query( $create_table_statement );

                return true;
            }
        }

        /** @method alter_table_query
         * Sends a query to the database and changes a table in the
         * database immediately, returning true. Note that unlike
         * this::create_table_query(), most of the query data isn't created
         * for you.
         * 
         * Optionally return a query string by setting the
         * $return_string_only argument to true. This is optional, instead
         * of sending a query directly to the database.
         * 
         * @param $table
         * @param $alter_specification_keywords -- Keywords may be ADD,
         *      ALTER, DROP, etc.
         * @param $alter_specifications
         * @param bool $check_existence
         * @param bool $return_string_only
         * @param int $wait
         * @param bool $no_wait
         * @return string|false
         */
        function alter_table_query(
            string $table,
            $alter_specification_keywords,
            $alter_specifications,
            bool $check_existence = false,
            bool $return_string_only = false,
            int $wait = -1,
            bool $no_wait = false ) {

            /* Definition ************************************************/
            $alter_table_statement;

            /* Processing ************************************************/
            /* Validation ************************************************/
            /* Validate Alter Specifications */
            $alter_specification_keywords =
                $this->validate_sql_alter_specifications(
                    $alter_specification_keywords,
                    $alter_specifications );

            if (
                [] === $alter_specification_keywords
                || empty( $alter_specification_keywords ) ) {

                return false;
            }

            /* Build Clause ---------------------------------------------*/
            $alter_table_statement =
                'ALTER TABLE ';

            /* Conditionally Add Table Check */
            if ( $check_existence ) {

                $alter_table_statement .= 'IF EXISTS ';
            }

            /* Add Table Name */
            $alter_table_statement .=
                $this->enclose_database_object_names( $table )
                . ' ';

            /* Conditionally Add WAIT or NO WAIT */
            if ( -1 < $wait ) {

                $alter_table_statement .=
                    'WAIT ' . strval( $wait ) . ' ';
            }
            elseif ( $no_wait ) {

                $alter_table_statement .=
                    'NOWAIT ';
            }

            /* Add Alter Specifications */
            foreach ( $alter_specification_keywords as $key => $name ) {

                $alter_table_statement .=
                    $alter_specification_keywords[ $key ] . ' '
                    . $alter_specifications[ $key ] . ', ';
            }

            // Remove Trailing ', '
            $alter_table_statement = substr(
                $alter_table_statement,
                0,
                strlen( $alter_table_statement ) - 2 );

            /* Return ****************************************************/
            if ( $return_string_only ) {

                return $alter_table_statement;
            }
            else {

                $this->pdo->query( $alter_table_statement );

                return true;
            }
        }

        /** @method drop_table_query
         * Sends a query to the database and removes a table from the
         * database immediately, returning true.
         * 
         * Optionally return a query string by setting the
         * $return_string_only argument to true. This is optional, instead
         * of sending a query directly to the database.
         * 
         * @param string|array $tables
         * @param bool $check_existence
         * @param bool $return_string_only
         * @return string
         */
        function drop_table_query(
            $tables,
            bool $check_existence = false,
            bool $return_string_only = false ) {

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
            if ( $return_string_only ) {

                return $drop_table_statement;
            }
            else {

                $this->pdo->query( $drop_table_statement );

                return true;
            }
        }

        /******************************************************************
        * Private Methods
        ******************************************************************/

        /*-----------------------------------------------------------------
         * General Validation/Building Methods
         *---------------------------------------------------------------*/

        /** @method validate_sql_data_types
         * Ensures the data type(s) provided are valid SQL data types. If
         * not, returns false so execution can be halted.
         * @param mixed string string[] $fields
         * @param mixed string string[] $comparison_operators
         * @param mixed string string[] $values
         * @param string[] $conditional_operators
         * @return string|false
         */
        private function validate_sql_data_types( $data_types ) {

            /* Definition ************************************************/
            $mysql_data_types =
                $this->read_json_file(
                    strstr(
                        __DIR__,
                        'classic_php',
                        true ) .
                    'classic_php/V0_3_0' .
                    '/classic_php_data_files/mysql_data_types.json' );
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
                $open_parenthesis_found = false;
                $close_parenthesis_found = false;

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

                    $parentheses_position = strlen( $data_types[ $key ] );
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
                                        $parentheses_position ) ) {
        
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

                    return false;
                }
            }

            /* Return ****************************************************/
            return $data_types;
        }

        /** @method validate_sql_alter_specifications
         * Validates the ALTER TABLE specifications provided are valid.
         * @param mixed string string[] $alter_table_action_keywords
         * @param mixed string string[] $alter_table_actions
         * @return array|false
         */
        private function validate_sql_alter_specifications(
            $alter_table_action_keywords,
            $alter_table_actions ) {

            /* Definition ************************************************/
            $mysql_alter_table_action_keywords =
                $this->read_json_file(
                    strstr(
                        __DIR__,
                        'classic_php',
                        true ) .
                    'classic_php/V0_3_0' .
                    '/classic_php_data_files/' .
                    'mysql_alter_table_action_keywords.json' );
            $is_action_valid = false;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force Parameters to be Arrays */
            // Force $alter_table_action_keywords to be Array
            if ( ! is_array( $alter_table_action_keywords ) ) {

                $alter_table_action_keywords =
                    [ $alter_table_action_keywords ];
            }
            
            // Force $alter_table_actions to be Array
            if ( ! is_array( $alter_table_actions ) ) {

                $alter_table_actions = [ $alter_table_actions ];
            }

            /* Validate $alter_table_action_keywords */
            if (
                ! $this->arrays->validate_data_types(
                    $alter_table_action_keywords,
                    'string' ) ) {

                return false;
            }
            elseif (
                [] === $alter_table_action_keywords
                || empty( $alter_table_action_keywords ) ) {

                return false;
            }
            
            /* Validate $alter_table_actions */
            if (
                ! $this->arrays->validate_data_types(
                    $alter_table_actions,
                    'string' ) ) {

                return false;
            }
            elseif (
                [] === $alter_table_actions
                || empty( $alter_table_actions ) ) {

                return false;
            }

            /* Validate $alter_table_action -----------------------------*/
            foreach( $alter_table_action_keywords as $key => $keyword ) {

                $is_action_valid = false;
                
                /* Force All Action Keywords to Uppercase */
                $alter_table_action_keywords[ $key ] =
                    strtoupper( $keyword );

                /* Compare $data_types to $mysql_data_types */
                foreach(
                    $mysql_alter_table_action_keywords
                    as $sql_action ) {

                    if (
                        $alter_table_action_keywords[ $key ] ===
                            strtoupper( $sql_action ) ) {

                        $is_action_valid = true;

                        break;
                    }
                }

                /* If No Valid Data Type Found, Mark $data_types Value */
                if ( ! $is_action_valid ) {

                    return false;
                }
            }

            /* Return ****************************************************/
            return $alter_table_action_keywords;
        }
    }
}
