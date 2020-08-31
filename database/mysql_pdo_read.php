<?php

namespace ClassicPHP {

    /* Class Using Aliases */
    use \PDO as PDO;

    /*
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

    /*

    - Query database data for PDO connections
    - Alter database data
    - Drop database data

    */

    /** Class: MySQLPDO_Read
     * Allows you to query a database safely using PDO.
     * Inherits From: ClassicPHP\MySQLPDO
     * Requires: \PDO
     * Inherited By: None
     *********************************************************************/
    class MySQLPDO_Read extends MySQLPDO {

        function __construct( PDO $pdo_connection ) {

            parent::__construct( $pdo_connection );

            $this->error = new ErrorHandling();
        }

        function create_selection_clause(
            $fields ) {

            /* Processing ************************************************/
            //
        }
    }
}
