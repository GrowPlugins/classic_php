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
        Write Queries:
            UPDATE table
                SET field = value
            WHERE field = value

            INSERT INTO table
            (fields)
            VALUES (values)

            DELETE table
            WHERE field = value

        Manage Queries:
            CREATE table
            (fields)
            VALUES (values)

            DROP table
    */

    /** Class: MySQLPDO_Write
     * Allows you more quickly change database data safely using PDO.
     * Inherits From: ClassicPHP\MySQLPDO
     * Requires: \PDO, ClassicPHP\ArrayProcessing
     * Inherited By: None
     */
    class MySQLPDO_Write extends MySQLPDO {

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

        /** @method create_update_clause
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
        function create_update_clause(
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

                $update_clause = 'UPDATE ' . $table . ' SET ';

                foreach ( $set_fields as $key => $set_field ) {

                    // Add Next Field, Operator, and Value, If All Exist
                    if (
                        array_key_exists( $key, $set_comparisons )
                        && array_key_exists( $key, $set_values ) ) {

                        $update_clause .=
                            $set_fields[ $key ] . ' '
                            . $set_comparisons[ $key ] . ' '
                            . $set_values[ $key ] . ', ';
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
            $where_clause;

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

        /** @method create_insert_into_clause
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
        function create_insert_into_clause(
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

                    $insert_into_clause .= $field . ', ';
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

                    $insert_into_clause .= $value . ', ';
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

        /* EXAMPLE METHODS **************************************************************/

        /** @method create_selection_clause
         * Creates a SELECT clause string for use within a selection
         * statement. Does not allow the use of subqueries in the clause.
         * Fields should be validated prior to using this method.
         * @param string[] $fields
         * @param mixed string[] string $functions
         * @return string
         */
        function create_selection_clause(
            array $fields,
            $functions = [''] ) {

            /* Definition ************************************************/
            $selection_clause;

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
            $from_clause;

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

                // Validate Each Join Type
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
            $group_by_clause;

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

            /* Else Return an Empty GROUP BY Clause */
            else {

                $group_by_clause = '';
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
            $having_clause;

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
            $limit_clause;

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
            $order_by_clause;

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

            /* Else Return an Empty GROUP BY Clause */
            else {

                $order_by_clause = '';
            }

            /* Return ****************************************************/
            return $order_by_clause;
        }
    }
}
