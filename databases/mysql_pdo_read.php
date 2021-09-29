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
if ( ! class_exists( '\ClassicPHP\MySQLPDO_Read' ) ) {

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

        /** @method build_select_clause
         * Creates a SELECT clause string for use within a selection
         * statement. Does not allow the use of subqueries in the clause.
         * Fields should be validated prior to using this method.
         * @param string[] $fields
         * @param mixed string[] string $functions
         * @return string
         */
        function build_select_clause(
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
                            $functions[ $key ] . '(';

                        if ( '*' === $field ) {

                            $selection_clause .=
                                $field . '), ';
                        }
                        else {

                            $selection_clause .=
                                $this->enclose_database_object_names(
                                    $field ) . '), ';
                        }
                    }

                    // Add Field without Function
                    else {

                        if ( '*' === $field ) {

                            $selection_clause .=
                                $field . ', ';
                        }
                        else {

                            $selection_clause .=
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

        /** @method build_from_clause
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
        function build_from_clause(
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

        /** @method build_group_by_clause
         * Creates a GROUP BY clause string for use within a selection
         * statement. Fields should be validated prior to using this
         * method.
         * @param string[] $fields
         * @return string
         */
        function build_group_by_clause(
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
         * method. It is highly suggested to use PDO parameter
         * placeholders (e.g., ':placeholder') for values, so you can
         * implement PDO prepared statements. However, this is not
         * required.
         * @param mixed string string[] $fields
         * @param mixed string string[] $comparison_operators
         * @param mixed $values
         * @param string[] $conditional_operators
         * @return string
         * @return false
         */
        function build_having_clause(
            $fields,
            $comparison_operators,
            $values,
            array $conditional_operators = ['AND'] ) {

            /* Definition ************************************************/
            $having_clause = '';

            /* Processing ************************************************/
            /* Build WHERE Clause, Since Having is the Same */
            $having_clause =
                $this->build_where_clause(
                    $fields,
                    $comparison_operators,
                    $values,
                    $conditional_operators );

            /* Replace WHERE with HAVING */
            $having_clause =
                str_replace( 'WHERE', 'HAVING', $having_clause );

            /* Return ****************************************************/
            return $having_clause;
        }

        /** @method build_limit_clause
         * Creates a LIMIT clause string for use within a selection
         * statement.
         * @param int $limit
         * @param int $offset
         * @return string
         */
        function build_limit_clause(
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

        /** @method build_order_by_clause
         * Creates a ORDER BY clause string for use within a selection
         * statement. Fields should be validated prior to using this
         * method.
         * @param string[] $fields
         * @return string
         */
        function build_order_by_clause(
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

        /******************************************************************
        * Private Methods
        ******************************************************************/

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
