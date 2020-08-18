<?php

namespace ClassicPHP {

    class ValidateMySQL {

        __construct() {

            //
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

        function validate_table_names(
            $table_names,
            $return_type = 'array' ) {

            /* Definition ************************************************/
            $pdo_statement;         // PDO_Statement object
            $existing_tables;       // Tables array (assoc)
            $table_found;           // Whether table exists
            $return_string;         // String to return if returning string

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            $return_type = $this->validate_argument_return_type(
                $return_type );

            /* Query Available Tables -----------------------------------*/
            $table_names = $this->validate_argument_values_array(
                $table_names,
                $return_type );

            $pdo_statement = $this->pdo->query( 'SHOW TABLES' );

            $pdo_statement->execute();

            $existing_tables = $pdo_statement->fetch ( PDO::FETCH_ASSOC );

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

                        $this->remove_array_value(
                            $table_names,
                            $table_name_key );
                    }
                    else {

                        return false;
                    }
                }
            }

            /* Return ****************************************************/
            if ( 'array' === $return_type ) {

                return $table_names;
            }
            elseif ( 'string' === $return_type ) {

                /*foreach ( $table_names as $table_name ) {

                    $return_string .= $table_name
                }*/

                return $return_string;
            }
            else {

                return true;
            }
        }

        function validate_field_names( $field_names ) {

            /* Definition ************************************************/
            $pdo_statement;     // PDO_Statement object
            $tables;            // Tables array (assoc);

            /* Processing ************************************************/
            $pdo_statement = $this->pdo->query('SHOW TABLES');

            $pdo_statement->execute();

            $tables = $pdo_statement->fetch(PDO::FETCH_ASSOC);
        }

        private function validate_argument_return_type( $return_type ) {

            /* Processing ************************************************/
            /* Prevent Case Invalidation */
            $return_type = strtolower( strval( $return_type ) );

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
            $return_type = 'array') {

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

        private function remove_array_value( &$array, $key ) {

        /* Processing ****************************************************/
            /* Use unset() Only If Array Key is String Data Type */
            if ( is_string( $key ) ) {

                unset( $array[ $key ] );
            }
            else {

                array_splice( $array, $key );
            }

            /* Return ****************************************************/
            return $array;
        }

        /** @method validate_array()
         * Verifies that a variable is an array, and (opotionally)
         * that every element in that array is of a specific type.
         * @param mixed $array
         * @param string $array_data_type_required
         * @return bool
         */
        private function validate_array(
            $array,
            $array_data_type_required = 'none') {

            /* Definition ********************************************/
            $allowed_data_type_values = [
                'none',
                'string',
                'char',
                'int',
                'integer',
                'long',
                'float',
                'double',
                'real',
                'bool',
                'null',
            ];      // Values allowed in $array_data_type_required
            $allowed_value_found = false;
            $array_data_types_allowed = true;

            /* Processing ********************************************/
            /* Validation -------------------------------------------*/
            /* Force $array_data_type_required to Be Allowed Value */
            // Determine If $array_data_type_required is Allowed Value
            foreach ($allowed_data_type_values as $allowed_data_type) {

                if ($array_data_type_required === $allowed_data_type) {

                    $allowed_value_found = true;
                }
            }

            // Force $array_data_type_required as Null Unless Allowed
            if (!$allowed_value_found) {

                $array_data_type_required = 'none';
            }

            /* Check Array for Validity -----------------------------*/
            /* Validate Array If Array */
            if (is_array($array)) {

                // Validate String Array
                if (
                    $array_data_type_required === 'string'
                    || $array_data_type_required === 'char') {

                    foreach ($array as $element) {

                        if (!is_string($element)) {

                            return false;
                        }
                    }
                }

                // Validate Int Array
                elseif (
                    $array_data_type_required === 'int'
                    || $array_data_type_required === 'integer'
                    || $array_data_type_required === 'long') {

                    foreach ($array as $element) {

                        if (!is_int($element)) {

                            return false;
                        }
                    }
                }

                // Validate Float Array
                elseif (
                    || $array_data_type_required === 'float'
                    || $array_data_type_required === 'double'
                    || $array_data_type_required === 'real') {

                    foreach ($array as $element) {

                        if (!is_float($element)) {

                            return false;
                        }
                    }
                }

                // Validate Boolean Array
                elseif (
                    || $array_data_type_required === 'bool') {

                    foreach ($array as $element) {

                        if (!is_bool($element)) {

                            return false;
                        }
                    }
                }

                // Otherwise is Valid
            }

            /* If Not Array Return False */
            else {

                return false;
            }

            /* Return ************************************************/
            return true;
        }
    }
}
