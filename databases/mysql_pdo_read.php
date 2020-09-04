<?php

namespace ClassicPHP {

    /* Class Using Aliases */
    use \PDO as PDO;

    /*
        Query:
            SELECT Function(fields) AS fieldNames
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

        /** @method create_selection_clause
         * Creates a selection clause string for use within a selection
         * statement.
         * @param mixed string[] string $table_names
         * @param string $return_type -- array, string, bool/boolean
         * @return string[]
         * @return string
         * @return bool
         */
        function create_selection_clause( $fields, $functions = [''] ) {

            /* Definition ************************************************/
            $selection_clause;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            if ( ! is_array( $fields ) ) {

                return false;
            }

            $functions = $this->validate_functions( $functions );

            if ( false === $functions ) {

                $functions = [''];
            }

            /* Build Clause ---------------------------------------------*/
            $selection_clause = 'SELECT ';

            foreach ( $fields as $key => $field ) {

                if (
                    is_key( $functions, $key )
                    && '' !== $functions[ $key ] ) {

                    $selection_clause .= $function . '(' $field . '), ';
                }
                else {

                    $selection_clause .= $field . ', ';
                }
            }

            // Remove Trailing ', '
            $selection_clause = substr(
                $selection_clause,
                0,
                strlen( $selection_clause ) - 2 );

            return $selection_clause;
        }

        /** @method validate_functions
         * Returns the input array of functions, with those that are
         * invalid removed. If $return_type is 'bool' and any function is
         * invalid, false is returned.
         * @param mixed string[] string $table_names
         * @param string $return_type -- array, bool/boolean
         * @return string[]
         * @return bool
         */
        private function validate_functions(
            $functions,
            $return_type = 'array' ) {

            /* Definition ************************************************/
            $validated_functions;
            $valid_functions = [
                'ABS',
                'ACOS',
                'ADDDATE',
                'ADDTIME',
                'AES_DECRYPT',
                'AES_ENCRYPT',
                'ANY_VALUE',
                'ASCII',
                'ASIN',
                'ATAN',
                'ATAN2',
                'ATAN',
                'AVG',
                'BENCHMARK',
                'BIN',
                'BIN_TO_UUID',
                'BIT_AND',
                'BIT_COUNT',
                'BIT_LENGTH',
                'BIT_OR',
                'BIT_XOR',
                'CAN_ACCESS_COLUMN',
                'CAN_ACCESS_DATABASE',
                'CAN_ACCESS_TABLE',
                'CAN_ACCESS_USER',
                'CAN_ACCESS_VIEW',
                'CAST',
                'CEIL',
                'CEILING',
                'CHAR',
                'CHAR_LENGTH',
                'CHARACTER_LENGTH',
                'CHARSET',
                'COALESCE',
                'COERCIBILITY',
                'COLLATION',
                'COMPRESS',
                'CONCAT',
                'CONCAT_WS',
                'CONNECTION_ID',
                'CONV',
                'CONVERT',
                'CONVERT_TZ',
                'COS',
                'COT',
                'COUNT',
                'COUNT(DISTINCT)',
                'CRC32',
                'CUME_DIST',
                'CURDATE',
                'CURRENT_DATE',
                'CURRENT_ROLE',
                'CURRENT_TIME',
                'CURRENT_TIMESTAMP',
                'CURRENT_USER',
                'CURTIME',
                'DATABASE',
                'DATE',
                'DATE_ADD',
                'DATE_FORMAT',
                'DATE_SUB',
                'DATEDIFF',
                'DAY',
                'DAYNAME',
                'DAYOFMONTH',
                'DAYOFWEEK',
                'DAYOFYEAR',
                'DEFAULT',
                'DEGREES',
                'DENSE_RANK',
                'ELT',
                'EXP',
                'EXPORT_SET',
                'EXTRACT',
                'ExtractValue',
                'FIELD',
                'FIND_IN_SET',
                'FIRST_VALUE',
                'FLOOR',
                'FORMAT',
                'FORMAT_BYTES',
                'FORMAT_PICO_TIME',
                'FOUND_ROWS',
                'FROM_BASE64',
                'FROM_DAYS',
                'FROM_UNIXTIME',
                'GeomCollection',
                'GeometryCollection',
                'GET_DD_COLUMN_PRIVILEGES',
                'GET_DD_CREATE_OPTIONS',
                'GET_DD_INDEX_SUB_PART_LENGTH',
                'GET_FORMAT',
                'GET_LOCK',
                'GREATEST',
                'GROUP_CONCAT',
                'GROUPING',
                'GTID_SUBSET',
                'GTID_SUBTRACT',
                'HEX',
                'HOUR',
                'ICU_VERSION',
                'IF',
                'IFNULL',
                'IN',
                'INET_ATON',
                'INET_NTOA',
                'INET6_ATON',
                'INET6_NTOA',
                'INSERT',
                'INSTR',
                'INTERNAL_AUTO_INCREMENT',
                'INTERNAL_AVG_ROW_LENGTH',
                'INTERNAL_CHECK_TIME',
                'INTERNAL_CHECKSUM',
                'INTERNAL_DATA_FREE',
                'INTERNAL_DATA_LENGTH',
                'INTERNAL_DD_CHAR_LENGTH',
                'INTERNAL_GET_COMMENT_OR_ERROR',
                'INTERNAL_GET_ENABLED_ROLE_JSON',
                'INTERNAL_GET_HOSTNAME',
                'INTERNAL_GET_USERNAME',
                'INTERNAL_GET_VIEW_WARNING_OR_ERROR',
                'INTERNAL_INDEX_COLUMN_CARDINALITY',
                'INTERNAL_INDEX_LENGTH',
                'INTERNAL_IS_ENABLED_ROLE',
                'INTERNAL_IS_MANDATORY_ROLE',
                'INTERNAL_KEYS_DISABLED',
                'INTERNAL_MAX_DATA_LENGTH',
                'INTERNAL_TABLE_ROWS',
                'INTERNAL_UPDATE_TIME',
                'INTERVAL',
                'IS_FREE_LOCK',
                'IS_IPV4',
                'IS_IPV4_COMPAT',
                'IS_IPV4_MAPPED',
                'IS_IPV6',
                'IS_USED_LOCK',
                'IS_UUID',
                'ISNULL',
                'JSON_ARRAY',
                'JSON_ARRAY_APPEND',
                'JSON_ARRAY_INSERT',
                'JSON_ARRAYAGG',
                'JSON_CONTAINS',
                'JSON_CONTAINS_PATH',
                'JSON_DEPTH',
                'JSON_EXTRACT',
                'JSON_INSERT',
                'JSON_KEYS',
                'JSON_LENGTH',
                'JSON_MERGE_PATCH',
                'JSON_MERGE_PRESERVE',
                'JSON_OBJECT',
                'JSON_OBJECTAGG',
                'JSON_OVERLAPS',
                'JSON_PRETTY',
                'JSON_QUOTE',
                'JSON_REMOVE',
                'JSON_REPLACE',
                'JSON_SCHEMA_VALID',
                'JSON_SCHEMA_VALIDATION_REPORT',
                'JSON_SEARCH',
                'JSON_SET',
                'JSON_STORAGE_FREE',
                'JSON_STORAGE_SIZE',
                'JSON_TABLE',
                'JSON_TYPE',
                'JSON_UNQUOTE',
                'JSON_VALID',
                'JSON_VALUE',
                'LAG',
                'LAST_INSERT_ID',
                'LAST_VALUE',
                'LCASE',
                'LEAD',
                'LEAST',
                'LEFT',
                'LENGTH',
                'LineString',
                'LN',
                'LOAD_FILE',
                'LOCALTIME',
                'LOCALTIMESTAMP',
                'LOCATE',
                'LOG',
                'LOG10',
                'LOG2',
                'LOWER',
                'LPAD',
                'LTRIM',
                'MAKE_SET',
                'MAKEDATE',
                'MAKETIME',
                'MASTER_POS_WAIT',
                'MAX',
                'MBRContains',
                'MBRCoveredBy',
                'MBRCovers',
                'MBRDisjoint',
                'MBREquals',
                'MBRIntersects',
                'MBROverlaps',
                'MBRTouches',
                'MBRWithin',
                'MD5',
                'MEMBER OF',
                'MICROSECOND',
                'MID',
                'MIN',
                'MINUTE',
                'MOD',
                'MONTH',
                'MONTHNAME',
                'MultiLineString',
                'MultiPoint',
                'MultiPolygon',
                'NAME_CONST',
                'NOW',
                'NTH_VALUE',
                'NTILE',
                'NULLIF',
                'OCT',
                'OCTET_LENGTH',
                'ORD',
                'PERCENT_RANK',
                'PERIOD_ADD',
                'PERIOD_DIFF',
                'PI',
                'Point',
                'Polygon',
                'POSITION',
                'POW',
                'POWER',
                'PS_CURRENT_THREAD_ID',
                'PS_THREAD_ID',
                'QUARTER',
                'QUOTE',
                'RADIANS',
                'RAND',
                'RANDOM_BYTES',
                'RANK',
                'REGEXP_INSTR',
                'REGEXP_LIKE',
                'REGEXP_REPLACE',
                'REGEXP_SUBSTR',
                'RELEASE_ALL_LOCKS',
                'RELEASE_LOCK',
                'REPEAT',
                'REPLACE',
                'REVERSE',
                'RIGHT',
                'ROLES_GRAPHML',
                'ROUND',
                'ROW_COUNT',
                'ROW_NUMBER',
                'RPAD',
                'RTRIM',
                'SCHEMA',
                'SEC_TO_TIME',
                'SECOND',
                'SESSION_USER',
                'SHA1',
                'SHA',
                'SHA2',
                'SIGN',
                'SIN',
                'SLEEP',
                'SOUNDEX',
                'SPACE',
                'SQRT',
                'ST_Area',
                'ST_AsBinary',
                'ST_AsWKB',
                'ST_AsGeoJSON',
                'ST_AsText',
                'ST_AsWKT',
                'ST_Buffer',
                'ST_Buffer_Strategy',
                'ST_Centroid',
                'ST_Contains',
                'ST_ConvexHull',
                'ST_Crosses',
                'ST_Difference',
                'ST_Dimension',
                'ST_Disjoint',
                'ST_Distance',
                'ST_Distance_Sphere',
                'ST_EndPoint',
                'ST_Envelope',
                'ST_Equals',
                'ST_ExteriorRing',
                'ST_GeoHash',
                'ST_GeomCollFromText',
                'ST_GeometryCollectionFromText',
                'ST_GeomCollFromTxt',
                'ST_GeomCollFromWKB',
                'ST_GeometryCollectionFromWKB',
                'ST_GeometryN',
                'ST_GeometryType',
                'ST_GeomFromGeoJSON',
                'ST_GeomFromText',
                'ST_GeometryFromText',
                'ST_GeomFromWKB',
                'ST_GeometryFromWKB',
                'ST_InteriorRingN',
                'ST_Intersection',
                'ST_Intersects',
                'ST_IsClosed',
                'ST_IsEmpty',
                'ST_IsSimple',
                'ST_IsValid',
                'ST_LatFromGeoHash',
                'ST_Latitude',
                'ST_Length',
                'ST_LineFromText',
                'ST_LineStringFromText',
                'ST_LineFromWKB',
                'ST_LineStringFromWKB',
                'ST_LongFromGeoHash',
                'ST_Longitude',
                'ST_MakeEnvelope',
                'ST_MLineFromText',
                'ST_MultiLineStringFromText',
                'ST_MLineFromWKB',
                'ST_MultiLineStringFromWKB',
                'ST_MPointFromText',
                'ST_MultiPointFromText',
                'ST_MPointFromWKB',
                'ST_MultiPointFromWKB',
                'ST_MPolyFromText',
                'ST_MultiPolygonFromText',
                'ST_MPolyFromWKB',
                'ST_MultiPolygonFromWKB',
                'ST_NumGeometries',
                'ST_NumInteriorRing',
                'ST_NumInteriorRings',
                'ST_NumPoints',
                'ST_Overlaps',
                'ST_PointFromGeoHash',
                'ST_PointFromText',
                'ST_PointFromWKB',
                'ST_PointN',
                'ST_PolyFromText',
                'ST_PolygonFromText',
                'ST_PolyFromWKB',
                'ST_PolygonFromWKB',
                'ST_Simplify',
                'ST_SRID',
                'ST_StartPoint',
                'ST_SwapXY',
                'ST_SymDifference',
                'ST_Touches',
                'ST_Transform',
                'ST_Union',
                'ST_Validate',
                'ST_Within',
                'ST_X',
                'ST_Y',
                'STATEMENT_DIGEST',
                'STATEMENT_DIGEST_TEXT',
                'STD',
                'STDDEV',
                'STDDEV_POP',
                'STDDEV_SAMP',
                'STR_TO_DATE',
                'STRCMP',
                'SUBDATE',
                'SUBSTR',
                'SUBSTRING',
                'SUBSTRING_INDEX',
                'SUBTIME',
                'SUM',
                'SYSDATE',
                'SYSTEM_USER',
                'TAN',
                'TIME',
                'TIME_FORMAT',
                'TIME_TO_SEC',
                'TIMEDIFF',
                'TIMESTAMP',
                'TIMESTAMPADD',
                'TIMESTAMPDIFF',
                'TO_BASE64',
                'TO_DAYS',
                'TO_SECONDS',
                'TRIM',
                'TRUNCATE',
                'UCASE',
                'UNCOMPRESS',
                'UNCOMPRESSED_LENGTH',
                'UNHEX',
                'UNIX_TIMESTAMP',
                'UpdateXML',
                'UPPER',
                'USER',
                'UTC_DATE',
                'UTC_TIME',
                'UTC_TIMESTAMP',
                'UUID',
                'UUID_SHORT',
                'UUID_TO_BIN',
                'VALIDATE_PASSWORD_STRENGTH',
                'VALUES',
                'VAR_POP',
                'VAR_SAMP',
                'VARIANCE',
                'VERSION',
                'WAIT_FOR_EXECUTED_GTID_SET',
                'WEEK',
                'WEEKDAY',
                'WEEKOFYEAR',
                'WEIGHT_STRING',
                'YEAR',
                'YEARWEEK',
            ];
            $valid_function_found = true;

            /* Processing ************************************************/
            /* Validation -----------------------------------------------*/
            if ( ! is_array( $functions ) ) {

                return false;
            }

            if ( 'array' !== $return_type ) {

                $return_type = 'bool';
            }

            /* Validate $functions are All $valid_functions */
            foreach ( $functions as $function ) {

                foreach( $valid_functions as $valid_function ) {

                    if ( $valid_function === $function ) {

                        $validated_functions[] = $function;
                        $valid_function_found = true;
                        break;
                    }
                    else {

                        $valid_function_found = false;
                    }
                }

                if (
                    ! $valid_function_found
                    && 'bool' === $return_type ) {

                    return false;
                }
                elseif ( ! $valid_function_found ) {

                    $validated_functions[] = '';
                }
            }

            /* Return ****************************************************/
            return $validated_functions;
        }
    }
}
