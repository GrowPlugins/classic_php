<?php

namespace ClassicPHP {

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
    require_once( CLASSIC_PHP_DIR . '/data_types/array_processing.php' );

    /*
        Read Queries:
            SELECT Function(fields) AS fieldNames
            FROM table
            JOIN table
                ON field = value
            GROUP BY fields
            HAVING field = value
            WHERE field = value
            LIMIT number, number
            ORDER BY fields
    */

    /** Class: MySQLPDO_Read
     * Helps you more quickly query a database safely using PDO.
     * Inherits From: ClassicPHP\MySQLPDO
     * Requires: \PDO, ClassicPHP\ArrayProcessing
     * Inherited By: None
     */
    class MySQLPDO_Read extends MySQLPDO {

        /******************************************************************
        * Properties
        ******************************************************************/

        protected $arrays;

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

            $this->arrays = new ArrayProcessing();
        }

        /** @method create_select_clause
         * Creates a SELECT clause string for use within a selection
         * statement. Does not allow the use of subqueries in the clause.
         * Fields should be validated prior to using this method.
         * @param string[] $fields
         * @param mixed string[] string $functions
         * @return string
         */
        function create_select_clause(
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
                            $functions[ $key ] . '(' . $field . '), ';
                    }

                    // Add Field without Function
                    else {

                        $selection_clause .= $field . ', ';
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

        /** @method create_from_clause
         * Creates a FROM clause string for use within a selection
         * statement. Does not allow the use of subqueries in the clause.
         * Tables and fields should be validated prior to using this
         * method.
         * @param string $table
         * @param string[] $joined_tables
         * @param string[] $join_types              // Eg, 'LEFT', 'RIGHT'
         * @param string[] $join_on_fields
         * @param string[] $join_on_comparisons     // Comparison Operators
         * @param string[] $join_on_values          // Values sought in ON
         * @return string
         */
        function create_from_clause(
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
            $from_clause = 'FROM ' . $table;

            /* Build Joined Tables into FROM Clause, If Given */
            if ( [] !== $joined_tables ) {

                foreach ( $joined_tables as $key => $joined_table ) {

                    // Add Join Type If Specified
                    if ( array_key_exists( $key, $join_types ) ) {

                        $from_clause .= ' ' . $join_types[ $key ];
                    }

                    // Add Table Join
                    $from_clause .=
                        ' JOIN ' . $joined_table;

                    // Add ON Subclause If Join Field, Comparison Operator,
                        // and Value Specified
                    if (
                        array_key_exists( $key, $join_on_fields )
                        && array_key_exists( $key, $join_on_comparisons )
                        && array_key_exists( $key, $join_on_values ) ) {

                        $from_clause .=
                            ' ON ' . $join_on_fields[ $key ] . ' '
                            . $join_on_comparisons[ $key ] . ' '
                            . $join_on_values[ $key ];
                    }
                }
            }

            /* Return ****************************************************/
            return $from_clause;
        }

        /** @method create_where_clause
         * Creates a WHERE clause string for use within a selection
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
        function create_where_clause(
            $fields,
            $comparison_operators,
            $values,
            array $conditional_operators = ['AND'] ) {

            /* Definition ************************************************/
            $where_clause = '';

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
            $where_clause .= $this->build_condition_list(
                $fields,
                $comparison_operators,
                $values,
                $conditional_operators );

            /* Return ****************************************************/
            return $where_clause;
        }

        /** @method create_group_by_clause
         * Creates a GROUP BY clause string for use within a selection
         * statement. Fields should be validated prior to using this
         * method.
         * @param string[] $fields
         * @return string
         */
        function create_group_by_clause(
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
                    $group_by_clause .= $field . ', ';
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

        /** @method create_having_clause
         * Creates a HAVING clause string for use within a selection
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
        function create_having_clause(
            $fields,
            $comparison_operators,
            $values,
            array $conditional_operators = ['AND'] ) {

            /* Definition ************************************************/
            $having_clause = '';

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
            $having_clause = 'HAVING ';

            /* Build HAVING Conditions */
            $having_clause .= $this->build_condition_list(
                $fields,
                $comparison_operators,
                $values,
                $conditional_operators );

            /* Return ****************************************************/
            return $having_clause;
        }

        /** @method create_limit_clause
         * Creates a LIMIT clause string for use within a selection
         * statement.
         * @param int $limit
         * @param int $offset
         * @return string
         */
        function create_limit_clause(
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

        /** @method create_order_by_clause
         * Creates a ORDER BY clause string for use within a selection
         * statement. Fields should be validated prior to using this
         * method.
         * @param string[] $fields
         * @return string
         */
        function create_order_by_clause(
            array $fields ) {

            /* Definition ************************************************/
            $order_by_clause = '';

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

                $order_by_clause = 'ORDER BY ';

                foreach ( $fields as $key => $field ) {

                    /* Build Fields into ORDER BY Clause */
                    $order_by_clause .= $field . ', ';
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

        /******************************************************************
        * Private Methods
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
        private function build_condition_list(
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

        /** @method read_json_file
         * Reads a JSON file and returns its contents as a valid JSON
         * object.
         * @param string $json_file
         * @param bool $return_json_array
         * @return mixed JSON array
         * @return bool
         */
        private function read_json_file(
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
