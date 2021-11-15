<?php

namespace ClassicPHP\V0_1_2;

/**************************************************************************
 * Class Definition -------------------------------------------------------
 *************************************************************************/
if ( ! class_exists( '\ClassicPHP\ArrayProcessing' ) ) {

    /** Class: ArrayProcessing
     * Allows you to manipulate arrays easier.
     * Inherits From: None
     * Requires: None
     * Inherited By: None
     */
    class ArrayProcessing {

        /******************************************************************
        * Public Methods
        ******************************************************************/

        /** @method remove_value
         * Removes a single value from an array. Uses unset() if the key
         * is a string, otherwise uses array_splice() to prevent unsetting
         * the array key altogether for keys of type int. Returns empty
         * array if only array value is null (assumed to be array with
         * last value removed).
         * @param mixed[] &$array
         * @param mixed string int $key
         * @return mixed[] $array
         */
        function remove_value( array &$array, $key ) {

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            if ( ! is_string( $key ) && ! is_int( $key ) ) {

                return false;
            }

            if ( ! array_key_exists( $key, $array ) ) {

                return false;
            }

            /* Use unset() Only If Array Key is String Data Type */
            if ( is_string( $key ) ) {

                unset( $array[ $key ] );
            }
            else {

                array_splice( $array, $key, 1 );
            }

            /* Make Array Empty If Only Remaining Element is Null */
            if (
                1 === count( $array )
                && null === $array[ array_keys( $array )[0] ] ) {

                $array = [];
            }

            /* Return ****************************************************/
            return $array;
        }

        /** @method mark_value_null
         * Replaces a single value in an array with a null. Primarily
         * meant to be used to mark an element for later removal.
         * @param mixed[] &$array
         * @param mixed string int $key
         * @return mixed[] $array
         */
        function mark_value_null( array &$array, $key ) {

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            if ( ! is_string( $key ) && ! is_int( $key ) ) {

                return false;
            }

            if ( ! array_key_exists( $key, $array ) ) {

                return false;
            }

            /* Nullify Element ------------------------------------------*/
            $array[ $key ] = null;

            /* Return ****************************************************/
            return $array;
        }

        /** @method remove_null_values
         * Removes all null elements from an array. Associative array
         * keys will be removed altogether if their element is null.
         * @param mixed[] &$array
         * @param mixed string int $key
         * @return mixed[] $array
         */
        function remove_null_values( array &$array ) {

            /* Processing ************************************************/
            /* Iterate Through $array via Array Pointer to Mimic
                foreach Loop, While Recognizing Changes to Array Size */
            while( null !== key( $array ) ) {

                if ( null === current( $array ) ) {

                    $this->remove_value( $array, key( $array ) );
                }

                // Increment Pointer Position
                next( $array );
            }

            /* Return ****************************************************/
            return $array;
        }

        /** @method validate_data_types()
         * Verifies that a variable is an array, and (optionally) that
         * every element in that array is one of a group of allowed
         * data types.
         * @param mixed $array
         * @param mixed string[] string $allowed_data_types
         * @return bool
         */
        function validate_data_types(
            $array,
            $allowed_data_types = 'any' ) {

            /* Definition ************************************************/
            $valid_data_types = [
                'any',
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
                'object',
                'class',
            ];
            $valid_data_type_found = false;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force $allowed_data_types to Array If String,
                Else Return False */
            if ( ! is_array( $allowed_data_types ) ) {

                if ( ! is_string( $allowed_data_types ) ) {

                    return false;
                }
                else {

                    $allowed_data_types = [ $allowed_data_types ];
                }
            }

            /* Force $allowed_data_types to Be Valid Data Types Only */
            foreach (
                $allowed_data_types as $key => $allowed_data_type ) {

                // Prepare to Find Positive Matches
                $valid_data_type_found = false;

                // Look for Positive Matches
                foreach (
                    $valid_data_types as $valid_data_type ) {

                    if ( $allowed_data_type === $valid_data_type ) {

                        $valid_data_type_found = true;
                        break;
                    }
                }

                // Force Data Type of 'any' If No Positive Matches
                if ( ! $valid_data_type_found ) {

                    $allowed_data_types[ $key ] = 'any';
                }
            }

            /* Check Array for Validity ---------------------------------*/
            /* Validate Array If Array */
            if ( is_array( $array ) ) {

                /* Verify Array Data Types Shown in $allowed_data_types */
                // Skip Data Type Validation If Any Data Type Will Do
                foreach( $allowed_data_types as $allowed_data_type ) {

                    if ( 'any' === $allowed_data_type ) {

                        return true;
                    }
                }

                // Compare Every Element's Data Type to $allowed_data_types
                foreach ( $array as $element ) {

                    // Prepare to Find Positive Matches
                    $valid_data_type_found = false;

                    // Look for Positive Matches
                    foreach (
                        $allowed_data_types as
                            $allowed_data_type ) {

                        //Determine If String Match
                        if (
                            'string' === $allowed_data_type
                            || 'char' === $allowed_data_type ) {

                            if ( is_string( $element ) ) {

                                $valid_data_type_found = true;
                                break;
                            }
                        }

                        // Determine If Int Match
                        elseif (
                            'int' === $allowed_data_type
                            || 'integer' === $allowed_data_type
                            || 'long' === $allowed_data_type ) {

                            if ( is_int( $element ) ) {

                                $valid_data_type_found = true;
                                break;
                            }
                        }

                        // Determine If Float Match
                        elseif (
                            'float' === $allowed_data_type
                            || 'double' === $allowed_data_type
                            || 'real' === $allowed_data_type ) {

                            if ( is_float( $element ) ) {

                                $valid_data_type_found = true;
                                break;
                            }
                        }

                        // Determine If Boolean Match
                        elseif ( 'bool' === $allowed_data_type ) {

                            if ( is_bool( $element ) ) {

                                $valid_data_type_found = true;
                                break;
                            }
                        }

                        // Determine If Null Match
                        elseif ( 'null' === $allowed_data_type ) {

                            if ( is_null( $element ) ) {

                                $valid_data_type_found = true;
                                break;
                            }
                        }

                        // Determine If Object Match
                        elseif (
                            'object' === $allowed_data_type
                            || 'class' === $allowed_data_type ) {

                            if ( is_object( $element ) ) {

                                $valid_data_type_found = true;
                                break;
                            }
                        }

                        // Otherwise is Invalid
                    }

                    // Return False On No Positive Matches
                    if ( ! $valid_data_type_found ) {

                        return false;
                    }

                }
            }

            /* If Not Array Return False */
            else {

                return false;
            }

            /* Return ****************************************************/
            return true;
        }

        /** @method trim_to_length
         * Trims the array so that the array is the length specified,
         * removing all elements after that length.
         * @param array $array
         * @param int $index
         * @return array $array
         */
        function trim_to_length( array $array, int $index ) {

            /* Definition ************************************************/
            $new_array = [];

            /* Processing ************************************************/
            for ( $i = 0; $i < count( $array ); $i++ ) {

                if ( $index > $i ) {

                    $new_array[] = $array[ $i ];
                }
                else {

                    break;
                }
            }

            /* Return ****************************************************/
            return $new_array;
        }

        /** @method shortest_array_length
         * Determines which array is the shortest, and returns the length
         * of that array.
         * @param array $arrays     // An array of arrays to measure
         * @return int $shortest_array_length
         */
        function shortest_array_length( array $arrays ) {

            /* Definition ************************************************/
            $shortest_array_length =
                count( $arrays[ array_key_first( $arrays ) ] );

            /* Processing ************************************************/
            foreach ( $arrays as $array ) {

                if ( $shortest_array_length > count( $array ) ) {

                    $shortest_array_length = count( $array );
                }
            }

            /* Return ****************************************************/
            return $shortest_array_length;
        }
    }
}
