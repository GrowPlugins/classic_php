<?php

namespace ClassicPHP {

    class ValidateArrays {

        /** @method validate_array()
         * Verifies that a variable is an array, and (opotionally)
         * that every element in that array is of a specific type.
         * @param mixed $array
         * @param string $array_data_type_required
         * @return bool
         */
        public function validate_array(
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
                    $array_data_type_required === 'float'
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
                    $array_data_type_required === 'bool') {

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