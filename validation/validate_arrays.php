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
                    'object' === $array_data_type_required ) {

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
         * @param string $allowed_data_types
         * @return bool
         */
        public function validate_data_types(
            $array,
            $allowed_data_types ) {

            /* Definition ************************************************/
            $valid_data_types = [
                '',
                'string',
                'int',
                'float',
                'bool',
                'null',
            ];
            $valid_data_type_found = false;
            $array_data_types_allowed = true;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            /* Force $allowed_data_types to Be Valid Data Types */
            foreach (
                $allowed_data_types as $key => $allowed_data_type ) {

                $valid_data_type_found = false;

                foreach (
                    $valid_data_types as $valid_data_type ) {

                    if ( $allowed_data_type === $valid_data_type ) {

                        $valid_data_type_found = true;
                        break;
                    }
                }

                if ( ! $valid_data_type_found ) {

                    $allowed_data_types[ $key ] = '';
                }
            }

            /* Check Array for Validity ---------------------------------*/
            /* Validate Array If Array */
            if ( is_array( $array ) ) {

                foreach (
                    $allowed_data_types as $allowed_data_type ) {

                    // Validate String Array
                    if ( 'string' === $allowed_data_type ) {

                        foreach ( $array as $element ) {

                            if ( ! is_string( $element ) ) {

                                return false;
                            }
                        }
                    }

                    // Validate Int Array
                    elseif ( 'int' === $allowed_data_type ) {

                        foreach ( $array as $element ) {

                            if ( ! is_int( $element ) ) {

                                return false;
                            }
                        }
                    }

                    // Validate Float Array
                    elseif ( 'float' === $allowed_data_type ) {

                        foreach ( $array as $element ) {

                            if ( ! is_float( $element ) ) {

                                return false;
                            }
                        }
                    }

                    // Validate Boolean Array
                    elseif ( 'bool' === $allowed_data_type ) {

                        foreach ( $array as $element ) {

                            if ( ! is_bool( $element ) ) {

                                return false;
                            }
                        }
                    }

                    // Otherwise is Valid
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
