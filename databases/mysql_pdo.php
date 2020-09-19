<?php

namespace ClassicPHP {

    /* Class Using Aliases */
    use \PDO as PDO;

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

        protected $pdo;

        /******************************************************************
        * Public Methods
        ******************************************************************/

        function __construct( PDO $pdo_connection ) {

            $this->pdo = $pdo_connection;
        }

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
            $tables_found;          // Array of tables saught that do exist
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
            $fields_found;          // Array of fields saught that do exist
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

        /** @method build_condition_list
         * Builds a list of fields, comparison operator, values, such as:
         * 'field = value AND field < value, ...'.
         * @param string[] $fields
         * @param string[] $comparison_operators
         * @param array $values
         * @param array $logic_operators
         * @return bool
         */
        protected function build_condition_list(
            array $fields = [],
            array $comparison_operators = [],
            array $values = [],
            array $logic_operators = [] ) {

            /* Definition ************************************************/
            $field_value_list = '';

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Validate $fields */
            if (
                ! $this->arrays->validate_data_types(
                    $fields,
                    'string' ) ) {

                $fields = [];
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

                $comparison_operators = [];
            }

            /* Validate $values */
            if (
                ! $this->arrays->validate_data_types(
                    $values,
                    ['string', 'int', 'float', 'bool'] ) ) {

                $values = [];
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

                $logic_operators = [];
            }

            /* Build Clause ---------------------------------------------*/
            /* Build List If Fields, Comparisons, and Values Exist */
            if (
                [] !== $fields
                && [] !== $comparison_operators
                && [] !== $values ) {

                foreach ( $fields as $key => $field ) {

                    // Append
                    if (
                        array_key_exists( $key, $comparison_operators )
                        && array_key_exists( $key, $values ) ) {

                        $field_value_list .=
                            $fields[ $key ] . ' '
                            . $comparison_operators[ $key ] . ' '
                            . $values[ $key ] . ' ';

                        // Append Conditional Operator
                        if ( array_key_exists( $key, $logic_operators ) ) {

                            $field_value_list .=
                                $logic_operators[ $key ] . ' ';
                        }

                        // If No Conditional Operator, Stop Building
                        else {

                            break;
                        }
                    }
                }

                // Remove Trailing ' '
                $field_value_list = substr(
                    $field_value_list,
                    0,
                    strlen( $field_value_list ) - 1 );
            }

            /* Return False Otherwise */
            else {

                return false;
            }

            /* Return ****************************************************/
            return $field_value_list;
        }

        /** @method encapsulate_names
         * Adds encapsulation to table and field names to allow spaces and
         * other special characters to exist within the names.
         * @param string $name                  // The table/field name
         * @param string $encapsulation_type    // 'backticks' or 'braces'
         */
        protected function encapsulate_names(
            string $name,
            string $encapsulation_type = 'backticks' ) {

            if ( 'backticks' === $encapsulation_type ) {

                return '`' . $name . '`';
            }
            elseif ( 'braces' === $encapsulation_type ) {

                return '[' . $name . ']';
            }
        }
    }
}
