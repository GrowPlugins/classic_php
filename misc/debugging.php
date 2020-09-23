<?php

namespace ClassicPHP {

    /** Class: Debugging
     * Allows you to debug code easier and faster.
     * Inherits From: None
     * Requires: None
     * Inherited By: None
     */
    class Debugging {

        /******************************************************************
        * Public Methods
        ******************************************************************/

        /** @method benchmark_execution
         * Executes a callback function and returns the time it took to
         * execute it, for comparison with other benchmarked callback
         * functions.
         * @param callable $callback_function
         * @param array $callback_function_parameters
         * @return float
         */
        function benchmark_execution(
            callable $callback_function,
            array $callback_function_parameters = [] ) {

            /* Declaration ***********************************************/
            $time1;
            $time2;

            /* Processing ************************************************/
            $time1 = floatval( microtime() );

            call_user_func_array(
                $callback_function,
                $callback_function_parameters);

            $time2 = floatval( microtime() );

            /* Return ****************************************************/
            return $time2 - $time1;
        }
    }
}
