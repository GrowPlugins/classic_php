<?php

namespace ClassicPHP {

    class ArrayProcessing {

        /** @method remove_array_value
         * Removes a single value from an array. Uses unset() if the key
         * is a string, otherwise uses array_splice() to prevent unsetting
         * the array key altogether for keys of type int.
         * @param mixed[] &$array
         * @param mixed string int $key
         * @return mixed[] $array
         */
        public function remove_array_value( &$array, $key ) {

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

            /* Return ****************************************************/
            return $array;
        }

        /** @method mark_array_value_null
         * Replaces a single value in an array with a null. Primarily
         * meant to be used to mark an element for later removal.
         * @param mixed[] &$array
         * @param mixed string int $key
         * @return mixed[] $array
         */
        public function mark_array_value_null( &$array, $key ) {

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

        /** @method remove_null_array_values
         * Removes all null elements from an array. Associative array
         * keys will be removed altogether if their element is null.
         * @param mixed[] &$array
         * @param mixed string int $key
         * @return mixed[] $array
         */
        public function remove_null_array_values( &$array ) {

            /* Processing ************************************************/
            foreach( $array as $array_key => $element ) {

                if ( null === $element ) {

                    $this->remove_array_value( $array, $array_key );
                }
            }

            /* Return ****************************************************/
            return $array;
        }
    }
}
