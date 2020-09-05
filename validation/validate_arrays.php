<?php

namespace ClassicPHP {

    class ValidateArrays {

        /** @method validate_is_array()
         * Verifies that a variable is an array, and (optionally) that
         * every element in that array is of a specific type.
         * @param mixed $array
         * @param string $array_data_type_required
         * @return bool
         */
        public function validate_is_array(
            $array,
            string $array_data_type_required = 'none' ) {

            /* Definition ************************************************/
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
                'object',
                'class',
            ];      // Values allowed in $array_data_type_required
            $allowed_value_found = false;
            $array_data_types_allowed = true;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force $array_data_type_required to Be Allowed Value */
            // Determine If $array_data_type_required is Allowed Value
            foreach (
                $allowed_data_type_values as $allowed_data_type ) {

                if (
                    $array_data_type_required ===
                        $allowed_data_type ) {

                    $allowed_value_found = true;
                }
            }

            // Force $array_data_type_required as 'none' Unless Allowed
            if ( ! $allowed_value_found ) {

                $array_data_type_required = 'none';
            }

            /* Check Array for Validity ---------------------------------*/
            /* Validate Array If Array */
            if ( is_array( $array ) ) {

                // Validate String Array
                if (
                    'string' === $array_data_type_required
                    || 'char' === $array_data_type_required ) {

                    foreach ( $array as $element ) {

                        if ( ! is_string( $element ) ) {

                            return false;
                        }
                    }
                }

                // Validate Int Array
                elseif (
                    'int' === $array_data_type_required
                    || 'integer' === $array_data_type_required
                    || 'long' === $array_data_type_required) {

                    foreach ( $array as $element ) {

                        if ( ! is_int( $element ) ) {

                            return false;
                        }
                    }
                }

                // Validate Float Array
                elseif (
                    'float' === $array_data_type_required
                    || 'double' === $array_data_type_required
                    || 'real' === $array_data_type_required ) {

                    foreach ( $array as $element ) {

                        if ( ! is_float( $element ) ) {

                            return false;
                        }
                    }
                }

                // Validate Boolean Array
                elseif (
                    'bool' === $array_data_type_required ) {

                    foreach ( $array as $element ) {

                        if ( ! is_bool( $element ) ) {

                            return false;
                        }
                    }
                }

                // Validate Null Array
                elseif (
                    'null' === $array_data_type_required ) {

                    foreach ( $array as $element ) {

                        if ( ! is_null( $element ) ) {

                            return false;
                        }
                    }
                }

                // Validate Class Array
                elseif (
                    'object' === $array_data_type_required
                    || 'class' === $array_data_type_required ) {

                    foreach ( $array as $element ) {

                        if ( ! is_object( $element ) ) {

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

            /* Return ****************************************************/
            return true;
        }

        /** @method validate_data_types()
         * Verifies that a variable is an array, and (optionally) that
         * every element in that array is one of a group of allowed
         * data types.
         * @param mixed $array
         * @param mixed string[] string $allowed_data_types
         * @return bool
         */
        public function validate_data_types(
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
    }
}
