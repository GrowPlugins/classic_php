<?php

namespace ClassicPHP {

    class ArrayProcessing {

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

        public function remove_null_array_values( &$array ) {

            echo '<pre>';
            print_r($array);
            echo '</pre>';

            /* Processing ************************************************/
            foreach( $array as $array_key => $element ) {

                if ( null === $element ) {

                    unset( $array[ $array_key ] );
                }
            }

            /* Return ****************************************************/
            return $array;
        }
    }
}
