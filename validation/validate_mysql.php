<?php

namespace ClassicPHP {

    use \PDO;

    if ( ! defined( 'CLASSIC_PHP_DIR' ) ) {

        $dir = strstr( __DIR__, 'classic_php', true ) . 'classic_php';

        define( 'CLASSIC_PHP_DIR', $dir );

        unset( $dir );
    }

    require_once( CLASSIC_PHP_DIR . '/data_types/array_processing.php' );

    class ValidateMySQL {

        use PDO;

        private $pdo;
        private $arrays;

        function __construct( PDO $pdo_connection ) {

            $this->pdo = $pdo_connection;
            $this->arrays = new ArrayProcessing();

            $this->error = new ErrorHandling();
        }

        /*

        fields
        tables
        values
        LIMIT numbers

        Query:
            SELECT fields
            FROM table
            JOIN table
                ON field = value
            GROUP BY fields
            HAVING field = value
            WHERE field = value
            LIMIT number, number

        Update:
            UPDATE table
            SET field = value

            INSERT INTO table
            (fields)
            VALUES (values)

            DELETE table
            WHERE field = value

        Create:
            CREATE table
            (fields)
            VALUES (values)

        Drop:
            DROP table
        */

        /** @method validate_table_names
         * Compares one or more table names to tables that exist in the
         * database. Returns either the input array of table names with
         * those that don't exist removed, or false, depending on the
         * return type specified in the arguments list.
         * @param mixed string[] string $table_names
         * @param string $return_type -- array, string, bool/boolean
         * @return string[]
         * @return string
         * @return boolean
         */
        function validate_table_names(
            $table_names,
            string $return_type = 'array' ) {

            /* Definition ************************************************/
            $pdo_statement;         // PDO_Statement object
            $existing_tables;       // Tables array (assoc)
            $returned_records;      // Temporary records variable
            $table_found;           // Whether table exists
            $return_string = '';    // String to return if returning string

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            $return_type = $this->validate_argument_return_type(
                $return_type );

            $table_names = $this->validate_argument_values_array(
                $table_names,
                'array' );

            /* Query Available Tables -----------------------------------*/
            /* Query Table Records */
            $pdo_statement = $this->pdo->query( 'SHOW TABLES' );

            $pdo_statement->execute();

            $returned_records = $pdo_statement->fetchAll( PDO::FETCH_NUM );

            /* Gather Table Names from Table Records */
            foreach ( $returned_records as $returned_record ) {

                $existing_tables[] = $returned_record[0];
            }

            /* Compare $table_names to Available Tables -----------------*/
            foreach ( $table_names as $table_name_key => $table_name ) {

                /* Search for Each Table Name in Existing Tables */
                $table_found = false;

                foreach ( $existing_tables as $existing_table ) {

                    if ( $table_name === $existing_table ) {

                        $table_found = true;
                        break;
                    }
                }

                /* Handle Instance Where Table Doesn't Exist */
                if ( ! $table_found ) {

                    if (
                        'array' === $return_type
                        || 'string' === $return_type ) {

                        // Mark Null for Future Removal
                        $this->arrays->mark_array_value_null(
                            $table_names,
                            $table_name_key );
                    }
                    else {

                        return false;
                    }
                }
            }

            /* Remove Tables That Don't Exist */
            $this->arrays->remove_null_array_values( $table_names );

            /* Return False No Matter What If $table_names is Now Empty */
            if ( 1 > count( $table_names ) ) {

                return false;
            }

            /* Return ****************************************************/
            if ( 'array' === $return_type ) {

                return $table_names;
            }
            elseif ( 'string' === $return_type ) {

                /* Generate $return_string */
                foreach ( $table_names as $table_name ) {

                    $return_string .= $table_name . ', ';
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
         * specified database table. Returns either the input array of
         * field names with those that don't exist removed, or false,
         * depending on the return type specified in the arguments list.
         * @param mixed string[] string $field_names
         * @param string $table_name
         * @param string $return_type -- array, string, bool/boolean
         * @param bool $validate_field_name
         * @return string[]
         * @return string
         * @return boolean
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

            /* Validate $field_name If $validate_table_name is True */
            if ( $validate_table_name ) {

                $table_name = $this->validate_table_names(
                    $table_name,
                    'string' );
            }

            /* Query Available Fields -----------------------------------*/
            /* Query Field Records */
            $pdo_statement = $this->pdo->query(
                'SHOW COLUMNS FROM ' . $table_name );

            $pdo_statement->execute();

            $returned_records =
                $pdo_statement->fetchAll( PDO::FETCH_NUM );

            /* Gather Field Names from Field Records */
            foreach ( $returned_records as $returned_record ) {

                $existing_fields[] = $returned_record[0];
            }

            /* Compare $field_names to Available Fields -----------------*/
            foreach ( $field_names as $field_name_key => $field_name ) {

                /* Search for Each Table Name in Existing Tables */
                $field_found = false;

                foreach ( $existing_fields as $existing_field ) {

                    if ( $field_name === $existing_field ) {

                        $field_found = true;
                        break;
                    }
                }

                /* Handle Instance Where Field Doesn't Exist */
                if ( ! $field_found ) {

                    if (
                        'array' === $return_type
                        || 'string' === $return_type ) {

                        // Mark Null for Future Removal
                        $this->arrays->mark_array_value_null(
                            $field_names,
                            $field_name_key );
                    }
                    else {

                        return false;
                    }
                }
            }

            /* Remove Fields That Don't Exist */
            $this->arrays->remove_null_array_values( $field_names );

            /* Return False No Matter What If $field_names is Now Empty */
            if ( 1 > count( $field_names ) ) {

                return false;
            }

            /* Return ****************************************************/
            if ( 'array' === $return_type ) {

                return $field_names;
            }
            elseif ( 'string' === $return_type ) {

                /* Generate $return_string */
                foreach ( $field_names as $field_name ) {

                    $return_string .= $field_name . ', ';
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

        private function validate_argument_return_type(
            string $return_type ) {

            /* Processing ************************************************/
            /* Prevent Case Invalidation */
            $return_type = strtolower( $return_type );

            /* Validate String Value */
            if (
                'array' !== $return_type
                && 'string' !== $return_type
                && 'boolean' !== $return_type
                && 'bool' !== $return_type ) {

                $return_type = 'array';
            }

            /* Return ****************************************************/
            return $return_type;
        }

        private function validate_argument_values_array(
            $values_array,
            string $return_type = 'array') {

            /* Processing ************************************************/
            /* Verify Array if Array */
            if ( ! is_array( $values_array ) ) {

                // Return False on Invalid Input and Boolean Return Type
                if (
                    'boolean' === $return_type
                    || 'bool' === $return_type ) {

                    return false;
                }

                elseif( 'string' === $return_type ) {

                    return strval( $values_array );
                }

                else {

                    return [ $values_array ];
                }
            }

            /* Return ****************************************************/
            return $values_array;
        }
    }
}
