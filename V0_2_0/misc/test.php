<?php

namespace ClassicPHP\V0_2_0;

/**************************************************************************
 * Class Definition -------------------------------------------------------
 *************************************************************************/
if ( ! class_exists( '\ClassicPHP\Test' ) ) {

/** Class: Test
 * Allows you to test/debug code easier and faster.
 * Inherits From: None
 * Requires: None
 * Inherited By: None
 */
class Test {

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

    /** @method unit_test
     * Executes a callback function and returns the time it took to
     * execute it, for comparison with other benchmarked callback
     * functions.
     * @param callable $callback_function
     * @param array $callback_function_parameters
     * @return float
     */
    function unit_test(
        callable $callback_function,
        $expected_result,
        array $callback_function_parameters = [] ) {

        /* Declaration ***********************************************/
        $result;
        $result_message;
        $result_var_dump;
        $expected_result_var_dump;

        /* Processing ************************************************/
        /* Execute Function/Method */
        try {

            $result = call_user_func_array(
                $callback_function,
                $callback_function_parameters);
        }
        catch (\Error $e) {

            return
                'Error Thrown: ' . $e->getCode() . '. '
                . $e->getMessage() . '. Thrown in ' . $e->getFile()
                . ', line ' . $e->getLine();
        }
        catch (\Exception $e) {

            return
                'Error Thrown: ' . $e->getCode() . '. '
                . $e->getMessage() . '. Thrown in ' . $e->getFile()
                . ', line ' . $e->getLine();
        }

        /* Test Result of Execution ---------------------------------*/
        /* Prepare Test Result Message Based on Result Found */
        if ( $result === $expected_result ) {

            $result_message = 'Test Passed! ';
        }
        elseif ( $result == $expected_result ) {

            $result_message = 'Partially Passed. ';
        }
        else {
            $result_message = 'Test Failed. ';
        }

        /* Gather Result Var Dump Info */
        // Get Var Dump of $expected_result Variable
        ob_start();

        var_dump( $expected_result );

        $expected_result_var_dump = ob_get_contents();

        // Get Var Dump of $result Variable
        ob_clean();

        var_dump( $result );

        $result_var_dump = ob_get_clean();

        /* Append Result Info */
        $result_message .=
            "\nExpected Result: { " . $expected_result_var_dump
            . "}\n Result: { " . $result_var_dump . '}';

        /* Return ****************************************************/
        return $result_message;
    }
}
