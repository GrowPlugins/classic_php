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
require_once( CLASSIC_PHP_DIR . '/data_types/array_processing.php' );

/**************************************************************************
 * Class Definition -------------------------------------------------------
 *************************************************************************/
if ( ! class_exists( '\ClassicPHP\MySQLPDO' ) ) {

    /** Class: MySQLPDO
     * Allows you to validate table names, field names, and limits with a
     * PDO connection.
     * Inherits From: None
     * Requires: \PDO, ClassicPHP\ArrayProcessing
     * Inherited By: ClassicPHP\MySQLPDO_Read
     */
    class MySQLPDO {

        /******************************************************************
        * Properties
        ******************************************************************/

        const DEFAULT_VALUE = "DEFAULT";

        protected PDO $pdo;
        protected ArrayProcessing $arrays;

        /******************************************************************
        * Public Methods
        ******************************************************************/

        function __construct( PDO $pdo_connection ) {

            $this->pdo = $pdo_connection;
            $this->arrays = new ArrayProcessing();
        }

        /*-----------------------------------------------------------------
         * General Database Query Methods
         *---------------------------------------------------------------*/

        /** @method query_database_tables
         * Queries the database for available tables.
         * @return string[] $database_tables
         */
        function query_database_tables() {

            /* Definition ************************************************/
            $pdo_statement;
            $returned_records;
            $database_tables;

            /* Processing ************************************************/
            /* Query Table Records */
            $pdo_statement = $this->pdo->query( 'SHOW TABLES' );

            $pdo_statement->execute();

            $returned_records = $pdo_statement->fetchAll( PDO::FETCH_NUM );

            /* Gather Table Names from Table Records */
            foreach ( $returned_records as $returned_record ) {

                $database_tables[] = $returned_record[0];
            }

            /* Return ****************************************************/
            return $database_tables;
        }

        /** @method query_table_fields
         * Queries the database table for available fields.
         * @param string $table
         * @param bool $validate_table_name
         * @return string[] $table_fields
         */
        function query_table_fields(
            string $table,
            bool $validate_table_name = false ) {

            /* Definition ************************************************/
            $pdo_statement;
            $returned_records;
            $table_fields;
            $is_valid_table;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            if ( true === $validate_table_name ) {

                if (
                    false ===
                        $this->validate_table_names( $table, 'bool' ) ) {

                    return false;
                }
            }

            /* Get Table Fields -----------------------------------------*/
            /* Query Field Records */
            $pdo_statement = $this->pdo->query(
                'SHOW COLUMNS FROM ' . $table );

            $returned_records =
                $pdo_statement->fetchAll( PDO::FETCH_NUM );

            /* Gather Field Names from Field Records */
            foreach ( $returned_records as $returned_record ) {

                $table_fields[] = $returned_record[0];
            }

            /* Return ****************************************************/
            return $table_fields;
        }

        /*-----------------------------------------------------------------
         * Database Object, or Object Attribute, Name Validation Methods
         *---------------------------------------------------------------*/

        /** @method validate_table_names
         * Compares one or more table names to tables that exist in the
         * database. $return_type determines how correct table names will
         * be returned.
         * If $return_type is 'array', tables specified which
         * do exist will have their names returned in an array. If
         * $return_type is 'string', tables specified which do exist will
         * be returned in a comma-separated list. If $return_type
         * is 'bool',  all fields specified must exist for true to be
         * returned, otherwise false will be returned instead.
         * @param mixed string[] string $table_names
         * @param string $return_type -- array, string, bool/boolean
         * @return string[]
         * @return string
         * @return bool
         */
        function validate_table_names(
            $table_names,
            string $return_type = 'array' ) {

            /* Definition ************************************************/
            $pdo_statement;         // PDO_Statement object
            $existing_tables;       // Tables array (assoc)
            $returned_records;      // Temporary records variable
            $table_found;           // Whether table exists
            $tables_found = [];     // Array of tables saught that do exist
            $return_string = '';    // String to return if returning string

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            $return_type = $this->validate_argument_return_type(
                $return_type );

            $table_names = $this->validate_argument_values_array(
                $table_names,
                'array' );

            /* Query Available Tables -----------------------------------*/
            $existing_tables = $this->query_database_tables();

            /* Compare $table_names to Available Tables -----------------*/
            foreach ( $table_names as $table_name_key => $table_name ) {

                /* Search for Each Table Name in Existing Tables */
                $table_found = false;

                foreach ( $existing_tables as $existing_table ) {

                    if ( $table_name === $existing_table ) {

                        $tables_found[] = $table_name;
                        $table_found = true;
                        break;
                    }
                }

                /* Handle Instance Where Table Doesn't Exist */
                if ( ! $table_found ) {

                    if (
                        ! 'array' === $return_type
                        && ! 'string' === $return_type ) {

                        return false;
                    }
                }
            }

            /* Return False No Matter What If $tables_found is Empty */
            if ( 1 > count( $tables_found ) ) {

                return false;
            }

            /* Return ****************************************************/
            if ( 'array' === $return_type ) {

                return $tables_found;
            }
            elseif ( 'string' === $return_type ) {

                /* Generate $return_string */
                foreach ( $tables_found as $table_found ) {

                    $return_string .= $table_found . ', ';
                }

                // Remove Trailing ', '
                $return_string = substr(
                    $return_string,
                    0,
                    strlen( $return_string ) - 2 );

                return $return_string;
            }
            else {

                return true;
            }
        }

        /** @method validate_field_names
         * Compares one or more field names to fields that exist in the
         * specified database table. $return_type determines how correct
         * field names will be returned.
         * If $return_type is 'array', fields specified which
         * do exist will have their names returned in an array. If
         * $return_type is 'string', fields specified which do exist will
         * be returned in a comma-separated list. If $return_type
         * is 'bool', all fields specified must exist for true to be
         * returned, otherwise false will be returned instead.
         * @param mixed string[] string $field_names
         * @param string $table_name
         * @param string $return_type -- array, string, bool/boolean
         * @param bool $validate_table_name
         * @return string[]
         * @return string
         * @return bool
         */
        function validate_field_names(
            $field_names,
            string $table_name,
            string $return_type = 'array',
            bool $validate_table_name = false) {

            /* Definition ************************************************/
            $pdo_statement;         // PDO_Statement object
            $existing_fields;       // Fields array (assoc)
            $returned_records;      // Temporary records variable
            $field_found;           // Whether field exists
            $fields_found = [];     // Array of fields saught that do exist
            $return_string = '';    // String to return if returning string

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $return_type */
            $return_type = $this->validate_argument_return_type(
                $return_type );

            /* Validate field_names */
            $field_names = $this->validate_argument_values_array(
                $field_names,
                $return_type );

            /* Validate $table_name If $validate_table_name is True */
            if ( $validate_table_name ) {

                $table_name = $this->validate_table_names(
                    $table_name,
                    'string' );

                if ( false === $table_name ) {

                    return false;
                }
            }

            /* Query Available Fields -----------------------------------*/
            $existing_fields = $this->query_table_fields( $table_name );

            /* Compare $field_names to Available Fields -----------------*/
            foreach ( $field_names as $field_name_key => $field_name ) {

                /* Search for Each Table Name in Existing Tables */
                $field_found = false;

                foreach ( $existing_fields as $existing_field ) {

                    if ( $field_name === $existing_field ) {

                        $fields_found[] = $field_name;
                        $field_found = true;
                        break;
                    }
                }

                /* Handle Instance Where Field Doesn't Exist */
                if ( ! $field_found ) {

                    if (
                        ! 'array' === $return_type
                        && ! 'string' === $return_type ) {

                        return false;
                    }
                }
            }

            /* Return False No Matter What If $field_names is Now Empty */
            if ( 1 > count( $fields_found ) ) {

                return false;
            }

            /* Return ****************************************************/
            if ( 'array' === $return_type ) {

                return $fields_found;
            }
            elseif ( 'string' === $return_type ) {

                /* Generate $return_string */
                foreach ( $fields_found as $field_found ) {

                    $return_string .= $field_found . ', ';
                }

                // Remove Trailing ', '
                $return_string = substr(
                    $return_string,
                    0,
                    strlen( $return_string ) - 2 );

                return $return_string;
            }
            else {

                return true;
            }
        }

        /*-----------------------------------------------------------------
         * Value Validation Methods
         *---------------------------------------------------------------*/

        /** @method validate_limits
         * Validates limit numbers so they are within acceptible ranges.
         * @param int $offset
         * @param int $row_limit
         * @return bool
         */
        function validate_limits(
            int $offset,
            int $row_limit ) {

            /* Return ****************************************************/
            if ( 0 <= $offset && 1 <= $row_limit ) {

                return true;
            }
            else {

                return false;
            }
        }

        /*-----------------------------------------------------------------
         * General Query Preparation Methods
         *---------------------------------------------------------------*/

        /** @method enclose_database_object_names
         * Adds name enclosure characters to table and field names to
         * allow spaces and other special characters to exist within
         * those names.
         * @param string $name                  // The table/field name
         * @param string $encapsulation_type    // 'backticks' or 'braces'
         */
        function enclose_database_object_names(
            string $name,
            string $encapsulation_type = 'backticks' ) {

            if ( 'backticks' === $encapsulation_type ) {

                return '`' . $name . '`';
            }
            elseif ( 'braces' === $encapsulation_type ) {

                return '[' . $name . ']';
            }
        }

        /** @method prepare_values_for_query
         * Prepares values for inclusion in an SQL query. Strings and dates
         * are enclosed in single quotes.
         * @param string $value                 // The value to be prepared
         */
        function prepare_values_for_query( $value ) {

            /* Processing ************************************************/
            /* Prepare $value as Class Constant Values */
            if ( self::DEFAULT_VALUE === $value ) {

                return $value;
            }

            /* Prepare $value Based on Data Type */
            elseif( is_string( $value ) ) {

                return '\'' . $value . '\'';
            }

            /* Return $value Without Preparing in All Other Cases */
            else {

                return $value;
            }
        }

        /** @method get_default_value
         * A getter to make it simpler to add DEFAULT_VALUE into a query.
         */
        function get_default_value() {

            /* Processing ************************************************/
            return self::DEFAULT_VALUE;
        }

        /******************************************************************
        * Private Methods
        ******************************************************************/

        /** @method validate_argument_return_type
         * Forces $return_type to be a string with any of the following
         * values:
         *      array
         *      string
         *      bool
         *      boolean
         * @param string $return_type
         * @return string $return_type
         */
        private function validate_argument_return_type(
            string $return_type ) {

            /* Processing ************************************************/
            /* Prevent Case Invalidation */
            $return_type = strtolower( $return_type );

            /* Validate String Value */
            if (
                'array' !== $return_type
                && 'string' !== $return_type
                && 'bool' !== $return_type
                && 'boolean' !== $return_type ) {

                $return_type = 'array';
            }

            /* Return ****************************************************/
            return $return_type;
        }

        /** @method validate_argument_values_array
         * Ensures $values_array is an array, or else an expected
         * alternative data type. When $return_type is bool, returns false
         * if $values_array is not an array. When $return_type is string,
         * returns a string from $values_array.
         * @param string $return_type
         * @return string $return_type
         */
        private function validate_argument_values_array(
            $values_array,
            string $return_type = 'array') {

            /* Processing ************************************************/
            /* Verify Array if Array */
            if ( ! is_array( $values_array ) ) {

                // Return False on Invalid Input and Boolean Return Type
                if (
                    'bool' === $return_type
                    || 'boolean' === $return_type ) {

                    return false;
                }

                // Return String When Invalid Input and String Return Type
                elseif( 'string' === $return_type ) {

                    return strval( $values_array );
                }

                // Return Array Otherwise (eg, Invalid Array Return Type)
                else {

                    return [ $values_array ];
                }
            }

            /* Return ****************************************************/
            return $values_array;
        }

        /******************************************************************
        * Protected Methods
        ******************************************************************/
       
        /*-----------------------------------------------------------------
         * General Clause Building Methods
         *---------------------------------------------------------------*/

        /** @method build_where_clause
         * Creates a WHERE clause string for use within a statement, such
         * as UPDATE or SELECT. Fields should be validated prior to using
         * this method. It is highly suggested to use PDO parameter
         * placeholders (e.g., ':placeholder') for values, so you can
         * implement PDO prepared statements. However, this is not
         * required.
         * @param mixed string string[] $fields
         * @param mixed string string[] $comparison_operators
         * @param mixed string string[] $values
         * @param string[] $conditional_operators
         * @return string
         */
        protected function build_where_clause(
            $fields,
            $comparison_operators,
            $values,
            $conditional_operators = ['AND'] ) {

            /* Definition ************************************************/
            $where_clause = '';
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

            /* Force $conditional_operators to be Array */
            if ( ! is_array( $conditional_operators ) ) {

                $conditional_operators = [ $conditional_operators ];
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

        /** @method build_order_by_clause
         * Creates a ORDER BY clause string for use within a statement.
         * Fields should be validated prior to using this method.
         * @param string[] $fields
         * @return string
         */
        protected function build_order_by_clause(
            $fields ) {

            /* Definition ************************************************/
            $order_by_clause = '';

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

                $order_by_clause = 'ORDER BY ';

                foreach ( $fields as $key => $field ) {

                    /* Build Fields into ORDER BY Clause */
                    $order_by_clause .=
                        $this->enclose_database_object_names(
                            $field ) . ', ';
                }

                // Remove Trailing ', '
                $order_by_clause = substr(
                    $order_by_clause,
                    0,
                    strlen( $order_by_clause ) - 2 );
            }

            /* Return ****************************************************/
            return $order_by_clause;
        }

        /** @method build_from_clause
         * Creates a FROM clause string for use within a statement. Does
         * not allow the use of subqueries in the clause. Tables and fields
         * should be validated prior to using this method.
         * @param string $table
         * @param string[] $joined_tables
         * @param string[] $join_types              // Eg, 'LEFT', 'RIGHT'
         * @param string[] $join_on_fields
         * @param string[] $join_on_comparisons     // Comparison Operators
         * @param string[] $join_on_values          // Values sought in ON
         * @return string
         */
        protected function build_from_clause(
            string $table,
            array $joined_tables = [],
            array $join_types = [],
            array $join_on_fields = [],
            array $join_on_comparisons = [],
            array $join_on_values = [] ) {

            /* Definition ************************************************/
            $from_clause = '';

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $join_types */
            if (
                $this->arrays->validate_data_types(
                    $join_types,
                    'string' ) ) {

                // Validate Each Join Type
                foreach ( $join_types as $key => $join_type ) {

                    $join_types[ $key ] =
                        strtoupper( $join_types[ $key ] );

                    if (
                        'LEFT' !== $join_types[ $key ]
                        && 'RIGHT' !== $join_types[ $key ]
                        && 'LEFT OUTER' !== $join_types[ $key ]
                        && 'RIGHT OUTER' !== $join_types[ $key ]
                        && 'INNER' !== $join_types[ $key ]
                        && 'CROSS' !== $join_types[ $key ]
                        && 'FULL' !== $join_types[ $key ] ) {

                        $join_types[ $key ] = 'INNER';
                    }
                }
            }
            else {

                $join_types = [];
            }

            /* Validate $join_on_fields */
            if (
                ! $this->arrays->validate_data_types(
                    $join_on_fields,
                    'string' ) ) {

                $join_on_fields = [];
            }

            /* Validate $join_on_comparisons */
            if (
                $this->arrays->validate_data_types(
                    $join_on_comparisons,
                    'string' ) ) {

                // Validate Each ON Comparison Operator
                foreach (
                    $join_on_comparisons as $key => $join_on_comparison ) {

                    if (
                        '=' !== $join_on_comparisons[ $key ]
                        && '<' !== $join_on_comparisons[ $key ]
                        && '>' !== $join_on_comparisons[ $key ]
                        && '<=' !== $join_on_comparisons[ $key ]
                        && '>=' !== $join_on_comparisons[ $key ]
                        && '<>' !== $join_on_comparisons[ $key ]
                        && '!=' !== $join_on_comparisons[ $key ] ) {

                        $join_on_comparisons[ $key ] = '=';
                    }
                }
            }
            else {

                $join_on_comparisons = [];
            }

            /* Validate $join_on_values */
            if (
                ! $this->arrays->validate_data_types(
                    $join_on_values,
                    ['string', 'int', 'float', 'bool'] ) ) {

                $join_on_values = [];
            }

            /* Build Clause ---------------------------------------------*/
            $from_clause =
                'FROM ' . $this->enclose_database_object_names( $table );

            /* Build Joined Tables into FROM Clause, If Given */
            if ( [] !== $joined_tables ) {

                foreach ( $joined_tables as $key => $joined_table ) {

                    // Add Join Type If Specified
                    if ( array_key_exists( $key, $join_types ) ) {

                        $from_clause .= ' ' . $join_types[ $key ];
                    }

                    // Add Table Join
                    $from_clause .=
                        ' JOIN ' . $this->enclose_database_object_names(
                            $joined_table );

                    // Add ON Subclause If Join Field, Comparison Operator,
                        // and Value Specified
                    if (
                        array_key_exists( $key, $join_on_fields )
                        && array_key_exists( $key, $join_on_comparisons )
                        && array_key_exists( $key, $join_on_values ) ) {

                        $from_clause .=
                            ' ON ' . $this->enclose_database_object_names(
                                $join_on_fields[ $key ] ) . ' '
                            . $join_on_comparisons[ $key ] . ' '
                            . $this->prepare_values_for_query(
                                $join_on_values[ $key ] );
                    }
                }
            }

            /* Return ****************************************************/
            return $from_clause;
        }

        /** @method build_limit_clause
         * Creates a LIMIT clause string for use within a statement.
         * @param int $limit
         * @param int $offset
         * @return string
         */
        protected function build_limit_clause(
            int $limit,
            int $offset = 0 ) {

            /* Definition ************************************************/
            $limit_clause = '';

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $limit */
            if ( 0 > $limit ) {

                return false;
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

        /*-----------------------------------------------------------------
         * General Validation/Building Methods
         *---------------------------------------------------------------*/

        /** @method build_condition_list
         * Builds a list of fields, comparison operator, values, such as:
         * 'field = value AND field < value, ...'.
         * @param string[] $fields
         * @param string[] $comparison_operators
         * @param array $values
         * @param array $logic_operators
         * @return string
         * @return false
         */
        protected function build_condition_list(
            array $fields = [],
            array $comparison_operators = [],
            array $values = [],
            array $logic_operators = [] ) {

            /* Definition ************************************************/
            $field_value_list = '';
            $smallest_input_array_length;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $fields */
            if (
                ! $this->arrays->validate_data_types(
                    $fields,
                    'string' ) ) {

                return false;
            }

            /* Validate $comparison_operators */
            if (
                $this->arrays->validate_data_types(
                    $comparison_operators,
                    'string' ) ) {

                // Validate Each Join Type
                foreach (
                    $comparison_operators as $key => $comparison ) {

                    if (
                        '=' !== $comparison_operators[ $key ]
                        && '<' !== $comparison_operators[ $key ]
                        && '>' !== $comparison_operators[ $key ]
                        && '<=' !== $comparison_operators[ $key ]
                        && '>=' !== $comparison_operators[ $key ]
                        && '<>' !== $comparison_operators[ $key ]
                        && '!=' !== $comparison_operators[ $key ] ) {

                        $comparison_operators[ $key ] = '=';
                    }
                }
            }
            else {

                return false;
            }

            /* Validate $values */
            if (
                ! $this->arrays->validate_data_types(
                    $values,
                    ['string', 'int', 'float', 'bool'] ) ) {

                return false;
            }

            /* Validate $logic_operators */
            if (
                $this->arrays->validate_data_types(
                    $logic_operators,
                    'string' ) ) {

                // Validate Each Join Type
                foreach (
                    $logic_operators as $key => $logic ) {

                    if (
                        'AND' !== $logic_operators[ $key ]
                        && 'OR' !== $logic_operators[ $key ] ) {

                        $logic_operators[ $key ] = 'AND';
                    }
                }
            }
            else {

                return false;
            }

            /* Trim Input Arrays to Smallest Input Array Length ---------*/
            /* Determine the Length of the Smallest Array */
            $smallest_input_array_length =
                $this->arrays->shortest_array_length(
                    [
                        $fields,
                        $comparison_operators,
                        $values
                    ] );
                    
            if (
                count( $logic_operators ) + 1
                < $smallest_input_array_length  ) {

                $smallest_input_array_length =
                    count( $logic_operators ) + 1;
            }

            /* Trim All Input Arrays */
            $fields = $this->arrays->trim_to_length(
                $fields,
                $smallest_input_array_length );

            $comparison_operators = $this->arrays->trim_to_length(
                $comparison_operators,
                $smallest_input_array_length );

            $values = $this->arrays->trim_to_length(
                $values,
                $smallest_input_array_length );

            // Trim $logic_operators to One Less Element Than Other Arrays
            $logic_operators = $this->arrays->trim_to_length(
                $logic_operators,
                $smallest_input_array_length - 1 );

            /* Build Clause ---------------------------------------------*/
            foreach ( $fields as $key => $field ) {

                // Append
                if (
                    array_key_exists( $key, $comparison_operators )
                    && array_key_exists( $key, $values ) ) {

                    $field_value_list .=
                        $this->enclose_database_object_names(
                            $fields[ $key ] ) . ' '
                        . $comparison_operators[ $key ] . ' '
                        . $this->prepare_values_for_query(
                            $values[ $key ] ) . ' ';

                    // Append Conditional Operator, If Not Last Key
                    if ( array_key_exists( $key, $logic_operators ) ) {

                        $field_value_list .=
                            $logic_operators[ $key ] . ' ';
                    }
                }
            }

            // Remove Trailing ' '
            $field_value_list = substr(
                $field_value_list,
                0,
                strlen( $field_value_list ) - 1 );

            /* Return ****************************************************/
            return $field_value_list;
        }

        /*-----------------------------------------------------------------
         * General Class-Specific Utility Methods
         *---------------------------------------------------------------*/

        /** @method read_json_file
         * Reads a JSON file and returns its contents as a valid JSON
         * object.
         * @param string $json_file
         * @param bool $return_json_array
         * @return mixed JSON array
         * @return bool
         */
        protected function read_json_file(
            $json_file,
            $return_json_array = false ) {

            /* Definition ************************************************/
            $json_string;

            /* Processing ************************************************/
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
            else {

                return false;
            }
        }
    }
}
